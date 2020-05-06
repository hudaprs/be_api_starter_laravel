<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>

<div>
    Hi {{ $name }},
    <br>
    @if($type == 'recover')
        Heres the token for resetting your account: <strong>{{ $password_reset_token }}</strong>
    @else 
        Your password has been reseted
    @endif
</div>

</body>
</html>