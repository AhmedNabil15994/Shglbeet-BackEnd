<!DOCTYPE html>
<html dir="{{ locale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>{{ __('order::api.orders.html_order.invoice_details') }}</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
    @if (locale() == 'ar')
        <link href="https://cdn.rtlcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    @endif

    <script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>

    <style>
        ._failed{ border-bottom: solid 4px red !important; }
        ._failed i{  color:red !important;  }

        ._success {
            box-shadow: 0 15px 25px #00000019;
            padding: 45px;
            width: 100%;
            text-align: center;
            margin: 40px auto;
            border-bottom: solid 4px #28a745;
        }

        ._success i {
            font-size: 55px;
            color: #28a745;
        }

        ._success h2 {
            margin-bottom: 12px;
            font-size: 40px;
            font-weight: 500;
            line-height: 1.2;
            margin-top: 10px;
        }

        ._success p {
            margin-bottom: 0px;
            font-size: 18px;
            color: #495057;
            font-weight: 500;
        }
    </style>

</head>

<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="message-box _success _failed">
                <i class="fa-solid fa-circle-xmark"></i>
                <h2>{{ __('order::frontend.orders.payment_failed') }}</h2>
            </div>
        </div>
    </div>

</div>

</body>
</html>
