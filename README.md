# osen-laravel-mpesa
Mpesa Controller class for Laravel PHP Framework

## Usage
### Configuration
Add the following to your .env file
    `MPESA_ENV="sandbox"\
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
    MPESA_TIMEOUT_URL=""`

### Controller
Copy MpesaController.php to the controllers directory

### Routing
    Route::prefix('mpesa')->group(function () {
      Route::any( '{path}', 'MpesaController@index');
      Route::any( '{path}/{trans}', 'MpesaController@index');
    });
