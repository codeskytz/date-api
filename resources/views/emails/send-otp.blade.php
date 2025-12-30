<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1 style="color: #333; margin-bottom: 20px;">Email Verification</h1>
        
        <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
            Hello,
        </p>
        
        <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
            Your One-Time Password (OTP) for email verification is:
        </p>
        
        <div style="background-color: #f9f9f9; border-left: 4px solid #007ACC; padding: 20px; margin: 30px 0; border-radius: 4px;">
            <p style="color: #333; font-size: 14px; margin: 0 0 10px 0;">
                <strong>OTP Code:</strong>
            </p>
            <p style="color: #007ACC; font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 0; text-align: center;">
                {{ $otp }}
            </p>
            <p style="color: #999; font-size: 12px; margin: 15px 0 0 0; text-align: center;">
                This code will expire in 10 minutes
            </p>
        </div>
        
        <p style="color: #666; font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
            <strong>Important:</strong> Do not share this code with anyone. We will never ask you for this code.
        </p>
        
        <div style="border-top: 1px solid #eee; padding-top: 20px; margin-top: 30px;">
            <p style="color: #999; font-size: 12px; margin: 0;">
                If you didn't request this OTP, please ignore this email.
            </p>
            <p style="color: #999; font-size: 12px; margin: 10px 0 0 0;">
                Best regards,<br>
                <strong>Date API Team</strong>
            </p>
        </div>
    </div>
</div>
