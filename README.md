# Laravel Mpesa
Mpesa Controller class for Laravel PHP Framework

## Pre Requisites
Please <a href="https://developer.safaricom.co.ke/" target="_blank" >create an app on Daraja</a> if you haven\'t.
For security purposes, and for the MPesa Instant Payment Notification to work, ensure your site is running over https(SSL).
You can <a href="https://developer.safaricom.co.ke/test_credentials" target="_blank" >generate sandbox test credentials here</a>.

The following configurations are required:

<table>
    <thead>
        <th>Option</th>
        <th>Possible Value</th>
        <th>Description</th>
    </thead>
    <tbody>
        <tr>
            <td>MPESA_ENV</td>
            <td>sandbox || live</td>
            <td>Your mpesa environment - sandbox if testing, live if in production</td>
        </tr>
        <tr>
            <td>MPESA_NAME</td>
            <td>Osen Concepts Kenya</td>
            <td>The name of the business as registered with Safaricom.</td>
        </tr>
        <tr>
            <td>MPESA_TYPE</td>
            <td>Identifier type</td>
            <td>1 Shortcode || 2 Till || 4 MSIDN</td>
        </tr>
        <tr>
            <td>MPESA_SHORTCODE</td>
            <td>Business Paybill/Till</td>
            <td></td>
        </tr>
        <tr>
            <td>MPESA_HO_NUMBER</td>
            <td>Head Office Number</td>
            <td>Main Paybill/Parent Number</td>
        </tr>
        <tr>
            <td>MPESA_APP_KEY</td>
            <td>Consumer Key</td>
            <td>Daraja Application Consumer Key</td>
        <tr>
        </tr>
            <td>MPESA_APP_SECRET</td>
            <td>Consumer Secret</td>
            <td>Daraja Application Consumer Secret</td>
        </tr>
        <tr>
            <td>MPESA_PASSKEY</td>
            <td>Online Passkey</td>
            <td></td>
        </tr>
        <tr>
            <td>MPESA_CREDENTIALS</td>
            <td>Security Credentials</td>
            <td></td>
        </tr>
        <tr>
            <td>MPESA_VALIDATE</td>
            <td>0 || Your\Namespaced\Controller@method</td>
            <td></td>
        </tr>
        <tr>
            <td>MPESA_CONFIRM</td>
            <td>0 || Your\Namespaced\Controller@method</td>
            <td></td>
        </tr>
        <tr>
            <td>MPESA_RECONCILE</td>
            <td>0 || Your\Namespaced\Controller@method</td>
            <td></td>
        </tr>
        <tr>
            <td>MPESA_TIMEOUT</td>
            <td>0 || Your\Namespaced\Controller@method</td>
            <td></td>
        </tr>
    </tbody>
</table>

## Usage
### Configuration
Add the following to your .env file

    MPESA_ENV="sandbox"
    MPESA_NAME=""
    MPESA_TYPE=""
    MPESA_SHORTCODE=""
    MPESA_HO_NUMBER=""
    MPESA_APP_KEY=""
    MPESA_APP_SECRET=""
    MPESA_PASSKEY=""
    MPESA_CREDENTIALS=""
    MPESA_VALIDATE="0"
    MPESA_CONFIRM="0"
    MPESA_RECONCILE="0"
    MPESA_TIMEOUT="0"


Both of these URLs output a success response. To actually validate/confirm the transaction before outputing the response, you can pass your callbacks as query variables. For instance if you have a controller called `MpesaCheck`, with a `validate` method for validation - or a Model called `MpesaPayment` you can register them as follows. Remember to add the full namespace of the class.

    MPESA_VALIDATE="App\Http\Controllers\MpesaCheck@validate"
    MPESA_VALIDATE="App\MpesaPayment@index"

### Controller
Copy Mpesa.php to the controllers directory, or create the controller via terminal by typing `php artisan make:controller Mpesa`, then copy the contents of the `Mpesa.php` file into your newly created controller.

### Routing
Add the following in your `~/routes/api.php` file to register our Mpesa IPN routes. You can replace `lipia` with a route name of your choice

    Route::prefix('lipia')->group(function () {
      Route::any( '/', 'Mpesa');
      Route::any( '{path}', 'Mpesa');
      Route::any( '{path}/{trans}', 'Mpesa');
    });

### URL Registration
Before you proceed, you need to register your validation and confirmation URLS. To do this, navigate to `https://yoursite.tld/api/lipia/register`

### Payment Processing
To process payment for an online checkout, send a POST request to `https://yoursite.tld:443/api/lipia/pay` with the following keys:

     'amount'
     'phone'
     'reference'
Note that `reference` is optional

### Reconciliation

By default, this contoller assumes no reconciliation. However, if you need to reconcile the Mpesa Payments, you can either create a Model or Controller with a method that handles the reconciliaton - by saving it to a database for instance. Remember to define this in the .env file in the format `MPESA_RECONCILE="Your\Namespaced\Controller@Method"` This method MUST return a boolean value( true or false).
You can take advantage of Laravel's inbuilt Model functions by creating a model, say, Mpayment, and calling the create method.

The following array keys and values is passed to your callback function - which is the body of the response returned by Mpesa:
<table>
    <thead>
        <th>Array Key</th>
        <th>Sample Value</th>
        <th>Description</th>
    </thead>
    <tbody>
        <tr>
            <td>MerchantRequestID</td>
            <td>19465-780693-1</td>
            <td>The unique merchant request ID of the transaction.</td>
        </tr>
        <tr>
            <td>CheckoutRequestID</td>
            <td>ws_CO_27072017154747416</td>
            <td>The unique checkout request ID of the transaction.</td>
        </tr>
        <tr>
            <td>ResultCode</td>
            <td>0</td>
            <td>Mpesa result code. O is OK, and anything else is false</td>
        </tr>
        <tr>
            <td>CallbackMetadata</td>
            <td>array( "Item" => [<br>
          [<br>
            "Name" => "Amount",<br>
            "Value" => 1<br>
          ],<br>
          [<br>
            "Name" => "MpesaReceiptNumber",<br>
            "Value":"LGR7OWQX0R"<br>
          ],<br>
          [<br>
            "Name" => "Balance"<br>
          ],<br>
          [<br>
            "Name" => "TransactionDate",<br>
            "Value" => 20170727154800<br>
          ],<br>
          [<br>
            "Name" => "PhoneNumber",<br>
            "Value" => 254721566839<br>
          ]<br>
        ] )</td>
        <td>Array of the callback meta data - actual details of transaction. Only present for a successsful transaction.</td>
        </tr>
    </tbody>
</table>

So, if your callback method is create(), for instance, it should take a single argument - $payment - which is an array, from which you can access the variables as follows;

    $MerchantRequestID  = $payment['$MerchantRequestID'];
    $CheckoutRequestID  = $payment['CheckoutRequestID'];
    $ResultCode         = $payment['ResultCode'];
    $ResultDesc         = $payment['ResultDesc'];
    $Amount             = $payment['CallbackMetadata']['Item'][0]['Value'];
    $MpesaReceiptNumber = $payment['CallbackMetadata']['Item'][1]['Value'];
    TransactionDate     = $payment['CallbackMetadata']['Item'][3]['Value'];
    PhoneNumber         = $payment['CallbackMetadata']['Item'][4]['Value'];
