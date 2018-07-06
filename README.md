# Laravel Mpesa
Mpesa Controller class for Laravel PHP Framework

## Usage
### Configuration
Add the following to your .env file

    MPESA_ENV="sandbox"\
    MPESA_NAME=""\
    MPESA_TYPE=""\
    MPESA_SHORTCODE=""\
    MPESA_HO_NUMBER=""\
    MPESA_APP_KEY=""\
    MPESA_APP_SECRET=""\
    MPESA_USERNAME=""\
    MPESA_PASSWORD=""\
    MPESA_PASSKEY=""\
    MPESA_CREDENTIALS=""\
    MPESA_VALIDATE="0"\
    MPESA_CONFIRM="0"\
    MPESA_RECONCILE="0"\
    MPESA_TIMEOUT="0"\


Both of these URLs output a success response. To actually validate/confirm the transaction before outputing the response, you can pass your callbacks as query variables. For instance if you have a controller called `MpesaCheck`, with a validate method for validation - or a Model called `MpesaPayment` you can register them as follows. Remember to add the full namespace of the class.
    MPESA_VALIDATE="App\Http\Controllerts\MpesaCheck@validate"\
    MPESA_VALIDATE="App\MpesaPayment@create"

### Controller
Copy Mpesa.php to the controllers directory, or create the controller via terminal by typing `php artisan make:controller Mpesa`, then copy the contents of the `Mpesa.php` file into your newly created controller.

### Routing
Add the following in your `~/routes/api.php` file to register our Mpesa IPN routes. You can replace `lipia` with a route of your choice

    Route::prefix('lipia')->group(function () {
      Route::any( '/', 'Mpesa');
      Route::any( '{path}', 'Mpesa');
      Route::any( '{path}/{trans}', 'Mpesa');
    });

### URL Registration
Before you proceed, you need to register your validation and confirmation URLS. To do this, navigate to `https://yoursite.tld/api/lipia/register`

### Payment Processing
To process payment for an online checkout, send a POST request to `https://yoursite.tld/:443/lipia/pay` with the following keys:
 'amount'\
 'phone'\
 'reference'\
Note that `reference` is optional

### Reconciliation

By default, this contoller assumes no reconcilliation. However, if you need to reconcile the Mpesa Payments, you can either create a Model or Controller with a method that handles the reconciliaton - by saving it to a database for instance. This method MUST return a boolean value( true or false).
You can take advantage of Laravel's inbuilt Model functions by creating a model, say, Mpayment, and calling the create method.

The following array keys and values is passed to your callback function - which is the body of the response returned by Mpesa:
<table>
    <thead>
        <th>Key</th>
        <th>Sample Value</th>
    </thead>
    <tbody>
        <tr>
            <td>MerchantRequestID</td>
            <td>19465-780693-1</td>
        </tr>
        <tr>
            <td>CheckoutRequestID</td>
            <td>ws_CO_27072017154747416</td>
        </tr>
        <tr>
            <td>ResultCode</td>
            <td>0</td>
        </tr>
        <tr>
            <td>CallbackMetadata</td>
            <td>array( "Item" => [
          [
            "Name" => "Amount",
            "Value" => 1
          ],
          [
            "Name" => "MpesaReceiptNumber",
            "Value":"LGR7OWQX0R"
          ],
          [
            "Name" => "Balance"
          ],
          [
            "Name" => "TransactionDate",
            "Value" => 20170727154800
          ],
          [
            "Name" => "PhoneNumber",
            "Value" => 254721566839
          ]
        ])</td>
        </tr>
    </tbody>
</table>

So, if your callback method is create(), for instance, it should take a single argument which is an array, from which you can access the variables as follows;

    $MerchantRequestID  = $payment['$MerchantRequestID'];
    $CheckoutRequestID  = $payment['CheckoutRequestID'];
    $ResultCode         = $payment['ResultCode'];
    $ResultDesc         = $payment['ResultDesc'];
    $Amount             = $payment['CallbackMetadata']['Item'][0]['Value'];
    $MpesaReceiptNumber = $payment['CallbackMetadata']['Item'][1]['Value'];
    TransactionDate     = $payment['CallbackMetadata']['Item'][3]['Value'];
    PhoneNumber         = $payment['CallbackMetadata']['Item'][4]['Value'];
