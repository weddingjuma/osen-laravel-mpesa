# osen-laravel-mpesa
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
    MPESA_VALIDATION_URL=""\
    MPESA_CONFIRMATION_URL=""\
    MPESA_CALLBACK_URL=""\
    MPESA_TIMEOUT_URL=""\

### Controller
Copy MpesaController.php to the controllers directory

### Routing
Add the following in your `~/routes/api.php` file to register our Mpesa IPN routes

    Route::prefix('mpesa')->group(function () {
      Route::any( '{path}', 'MpesaController@index');
      Route::any( '{path}/{trans}', 'MpesaController@index');
      Route::any( '{path}/{trans}/{opt}', 'MpesaController@index');
    });

### URL Registration
Before you proceed, you need to register your validation and confirmation URLS. To do this, navigate to `https://yoursite.tld/:443/mpesa/register`

The actual URLS we will be registering here are:
    `https://yoursite.tld/:443/mpesa/validate`
    `https://yoursite.tld/:443/mpesa/confirm`

### Payment Processing
To process payment for an online checkout, send a POST request to `https://yoursite.tld/:443/mpesa/pay` with the following keys:
 'amount'
 'phone'
 'reference'(optional)

 ### Reconciliation
 
