<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingTransactionRequest;
use App\Http\Resources\Api\BookingTransactionApiResource;
use App\Models\BookingTransaction;
use App\Models\Cosmetic;
use Illuminate\Http\Request;

class BookingTransactionnController extends Controller
{
    //
    public function store(StoreBookingTransactionRequest $request)
    {
        try {

            //Validasi request data
            $validateData = $request->validated();

            //Handle file upload 
            if ($request->hasFile('proof')){
                $filePath = $request->file('proof')->store('proofs', 'public');
                $validateData['proof'] = $filePath;
            }

            //Retrieve products and calculate total quantities and prices 
            $products =$request->input('cosmetic_ids');
            $totalQuantity = 0;
            $totalPrice = 0;

            $cosmeticIds = array_column($products, 'id');
            $cosmetics = Cosmetic::whereIn('id', $cosmeticIds)->get();

            foreach ($products as $product) {
                $cosmetic =$cosmetics->firstWhere('id', $product['id']);
                $totalQuantity += $product['quantity'];
                $totalPrice += $cosmetic->price *$product['quantity'];
            }

            $tax = 0.11 * $totalPrice;
            $grandTotal = $totalPrice + $tax;

            //Populate booking transaction data
            $validateData['total_amount'] = $grandTotal;
            $validateData['total_tax_amount'] = $tax;
            $validateData['sub_total_amount'] =$totalPrice;
            $validateData['is_paid'] = false;
            $validateData['booking_trx_id'] = BookingTransaction::generateUniqueTrxId();

            //Save total quantity in booking transactions
            $validateData['quantity'] = $totalQuantity;

            $bookingTransaction = BookingTransaction::create($validateData);

            //Create transaction details for each product
            foreach ($products as $product) {
                $cosmetic =$cosmetics->firstWhere('id', $product['id']);
                $bookingTransaction->transactionDetails()->create([
                    'cosmetic_id' => $product['id'],
                    'quantity' => $product['quantity'],
                    'price' => $cosmetic->price,
                ]);
            }

            //Return booking transaction details for each product
            return new BookingTransactionApiResource($bookingTransaction->load(['transactionDetails', 'transactionDetails.cosmetic']));

        } catch (\Exception $e) {
            return response()->json(['message'=> 'An error occurred', 'error'=> $e->getMessage()],500); 
        }
    }

    public function booking_details(Request $request) {

        $request->validate([
            'email' =>'required|string',
            'booking_trx_id' => 'required|string',
        ]);

        $booking = BookingTransaction::where('email', $request->email)
            ->where('booking_trx_id', $request->booking_trx_id)
            ->with([
                'transactionDetails',
                'transactionDetails.cosmetic',
                'transactionDetails.cosmetic.brand'
            ])
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }
        return new BookingTransactionApiResource($booking);
    }
}
