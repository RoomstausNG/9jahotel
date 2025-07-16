// DTO for initializing Paystack payment
export interface InitPaystackPaymentDTO {
  amount: number; // in Naira
  email: string;
  reference: string; // unique reference for the transaction
  callback_url?: string; // optional, Paystack will redirect here after payment
}
