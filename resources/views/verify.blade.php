<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>

<div>
    Hi {{ $name }},
    <br>
    Thank you for creating an account with us. Don't forget to complete your registration!
    <br>
    Copy this token: <strong>{{ $email_verification_token }}</strong>

    <br/>
</div>

</body>
</html>