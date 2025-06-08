<?php

/**
 * Example Usage of BookingInvoice Commission Calculation
 *
 * This file demonstrates how to use the new commission calculation functions
 * in the BookingInvoice model.
 */

// Example 1: Calculate and save commission for a booking invoice
function calculateCommissionForInvoice($invoiceId)
{
  $invoice = \App\Models\BookingInvoice::find($invoiceId);

  if ($invoice) {
    // This will calculate and save commission_amount and hotel_payable
    $invoice->calculateAndSaveCommission();

    echo "Commission calculated for invoice {$invoice->reference}:\n";
    echo "- Total Amount: " . number_format($invoice->amount) . "\n";
    echo "- Commission ({$invoice->getCommissionPercentage()}%): " . number_format($invoice->commission_amount) . "\n";
    echo "- Hotel Payable: " . number_format($invoice->hotel_payable) . "\n";
  }
}

// Example 2: Calculate commission without saving (for preview purposes)
function previewCommission($invoiceId)
{
  $invoice = \App\Models\BookingInvoice::find($invoiceId);

  if ($invoice) {
    $commissionAmount = $invoice->calculateCommissionAmount();
    $hotelPayable = $invoice->calculateHotelPayable();

    echo "Commission preview for invoice {$invoice->reference}:\n";
    echo "- Total Amount: " . number_format($invoice->amount) . "\n";
    echo "- Commission ({$invoice->getCommissionPercentage()}%): " . number_format($commissionAmount) . "\n";
    echo "- Hotel Payable: " . number_format($hotelPayable) . "\n";
  }
}

// Example 3: Bulk update commission for existing invoices
function updateCommissionForAllInvoices()
{
  $invoices = \App\Models\BookingInvoice::whereNull('commission_amount')
    ->orWhereNull('hotel_payable')
    ->with('hotel')
    ->get();

  $updated = 0;
  foreach ($invoices as $invoice) {
    $invoice->calculateAndSaveCommission();
    $updated++;
  }

  echo "Updated commission for {$updated} invoices.\n";
}

// Example 4: Using helper functions directly
function calculateCommissionWithHelpers($amount, $percentage)
{
  $commissionAmount = calculateCommissionAmount($amount, $percentage);
  $hotelPayable = calculateHotelPayable($amount, $commissionAmount);

  return [
    'commission_amount' => round($commissionAmount),
    'hotel_payable' => round($hotelPayable)
  ];
}

/**
 * Usage Examples:
 *
 * // Calculate and save commission for a specific invoice
 * calculateCommissionForInvoice(1);
 *
 * // Preview commission calculation without saving
 * previewCommission(1);
 *
 * // Update all invoices that don't have commission calculated
 * updateCommissionForAllInvoices();
 *
 * // Use helper functions directly
 * $result = calculateCommissionWithHelpers(10000, 15);
 * // Result: ['commission_amount' => 1500, 'hotel_payable' => 8500]
 */
