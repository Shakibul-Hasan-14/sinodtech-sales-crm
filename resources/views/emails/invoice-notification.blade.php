<!DOCTYPE html>
<html>
<body>
    <p>Hi {{ $sale->customer->name }},</p>
    <p>Thank you for your purchase! Your invoice is attached as a PDF.</p>
    <p><strong>Order Total:</strong> ${{ number_format($sale->total_amount, 2) }}</p>
    <p>The SinodTech Team</p>
</body>
</html>