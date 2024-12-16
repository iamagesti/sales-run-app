<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        #invoice-POS{
          box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
          padding: 2mm;
          margin: 0 auto;
          width: 44mm;
          background: #FFF;
        }
        ::selection {background: #f31544; color: #FFF;}
        ::moz-selection {background: #f31544; color: #FFF;}
        h1{
          font-size: 1.5em;
          color: #222;
        }
        h2{font-size: .9em;}
        h3{
          font-size: 1.2em;
          font-weight: 300;
          line-height: 2em;
        }
        p{
          font-size: .7em;
          color: #666;
          line-height: 1.2em;
        }
        #top, #mid, #bot{
          border-bottom: 1px solid #EEE;
        }
        #top{min-height: 100px;
        text-align: center;}
        #mid{min-height: 0}
        #bot{min-height: 50px;}
        #top .logo{
          height: 60px;
          width: 60px;
          display: inline-block;
          /* background: url(https://michaeltruong.ca/images/logo1.png) no-repeat; */
          /* background: url('/images/logo.png') no-repeat; */
          background-size: 60px 60px;
        }
        .clientlogo{
          float: left;
          height: 60px;
          width: 60px;
          /* background: url(http://michaeltruong.ca/images/client.jpg) no-repeat; */
          /* background: url('/images/logo.png') no-repeat; */
          background-size: 60px 60px;
          border-radius: 50px;
        }
        .info{
          display: block;
          margin-left: 0;
        }
        .title{
          float: right;
        }
        .title p{text-align: right;}
        table{
          width: 100%;
          border-collapse: collapse;
        }
        td{
        }
        .tabletitle{
          font-size: .5em;
          background: #EEE;
        }
        .service{border-bottom: 1px solid #EEE;}
        .item{width: 24mm;}
        .itemtext{font-size: .5em;}
        #legalcopy{
          margin-top: 5mm;
        }

        .print-btn {
        background-color: #000000;
        color: white;
        border: none;
        padding: 3px 8px;
        text-align: center;
        text-decoration: none;
        display: {{ $isDownload ? 'none' : 'inline-block' }};
        font-size: 12px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;

        /* float: right; */
         }

    /* Efek saat hover */
        .print-btn:hover {
        background-color: #444444;
        }

        .print-btn:active {
        background-color: #333333;
        }

        @page {
            size: 70mm 200mm;
            margin: 0;
        }

        @media print {
        .logo, .clientlogo {
         display: block;
         }
            /* Sembunyikan tombol Print saat pencetakan */
        .print-btn {
            display: none;
        }

        /* Sembunyikan semua elemen di luar elemen dengan ID #invoice-POS */
        body * {
            visibility: hidden;
        }

        #invoice-POS, #invoice-POS * {
            visibility: visible;
        }

        #invoice-POS {
            position: absolute;
            left: 0;
            top: 0;
        }
        }
    </style>
</head>
<body>
    <div id="invoice-POS">
        <center id="top">
            <div class="logo">
                <img src="data:image/png;base64,{{ $image }}" alt="Logo" style="width:100%; height: auto;">
            </div>
            <div class="info">
                <h2>Toko Kelontong</h2>
                <p>
                    Address: Jl Perumahan Depdagri<br>
                    Phone    : 085-555-5555<br>
                </p>
            </div>
        </center>
        <div id="mid">
            <div class="info">
                <p> Date : {{$sale->created_at}} <p>
            </div>
        </div>
        <div id="bot">
            <div id="table">
                <table>
                    <tr class="tabletitle">
                        <td class="item"><h2>Item</h2></td>
                        <td class="Hours"><h2>Qty</h2></td>
                        <td class="Rate"><h2>Total Amount</h2></td>
                    </tr>
                     <!-- Loop through each salesItem -->
                    @foreach($sale->items as $salesItem)
                     <tr class="service">
                            <td class="tableitem"><p class="itemtext">{{ $salesItem->product->name }}</p></td>
                            <td class="tableitem"><p class="itemtext">{{ $salesItem->quantity }}</p></td>
                            <td class="tableitem"><p class="itemtext">Rp.{{ number_format($salesItem->total_amount, 2, ',', '.') }}</p></td>
                     </tr>
                    @endforeach
                    <tr class="tabletitle">
                        <td></td>
                        <td class="Rate"><h2>Sub Total</h2></td>
                        <td class="payment"><h2>Rp.{{ number_format($sale->sub_total, 2, ',', '.') }}</h2></td>
                    </tr>
                    <tr class="tabletitle">
                        <td></td>
                        <td class="Rate"><h2>Discount Amount</h2></td>
                        <td class="payment"><h2>Rp.{{ number_format($sale->discount_amount, 2, ',', '.') }}</h2></td>
                    </tr>
                    <tr class="tabletitle">
                        <td></td>
                        <td class="Rate"><h2>Tax Amount</h2></td>
                        <td class="payment"><h2>Rp.{{ number_format($sale->tax_amount, 2, ',', '.') }}</h2></td>
                    </tr>
                    <tr class="tabletitle">
                        <td></td>
                        <td class="Rate"><h2>Grand Total</h2></td>
                        <td class="payment"><h2>Rp.{{ number_format($sale->grand_total, 2, ',', '.') }}</h2></td>
                    </tr>
                    <tr class="tabletitle">
                        <td></td>
                        <td class="Rate"><h2>Paid Amount</h2></td>
                        <td class="payment"><h2>Rp.{{ number_format($sale->paid_amount, 2, ',', '.') }}</h2></td>
                    </tr>
                    <tr class="tabletitle">
                        <td></td>
                        <td class="Rate"><h2>Change Amount</h2></td>
                        <td class="payment"><h2>Rp.{{ number_format($sale->change_amount, 2, ',', '.') }}</h2></td>
                    </tr>
                    <tr class="tabletitle">
                        <td></td>
                        <td class="Rate"><h2>Payment Method</h2></td>
                        <td class="payment"><h2>{{$sale->payment_method}}</h2></td>
                    </tr>
                </table>
            </div>
            <div id="legalcopy">
                <p>Cashier: {{$sale->user->name}}<br></p>
                <p> Note : {{$sale->notes}} <p>
                <p class="legal"><strong>Thank you!</strong> Please visit again.</p>
            </div>
        </div>
        @if(!$isDownload)
        <button class="print-btn" onclick="window.print()">Print</button>
        @endif
    </div>

</body>
</html>
