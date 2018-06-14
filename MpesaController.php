<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MpesaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $endpoint = ( getenv( 'MPESA_ENV' ) == 'live' ) ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $credentials = base64_encode( getenv( 'MPESA_APP_KEY' ).':'.getenv( 'MPESA_APP_SECRET' ) );

        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $endpoint );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Basic '.$credentials ) );
        curl_setopt( $curl, CURLOPT_HEADER, false );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
        $curl_response = curl_exec( $curl );
        
        $this->token = json_decode( $curl_response )->access_token;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request, $path = null, $transID = 0  )
    {
        switch ( $path ) {
            case 'validate':
                return array( 
                  'ResponseCode'  => 0, 
                  'ResponseDesc'  => 'Success',
                  'ThirdPartyTransID'   => $transID
                 );
                // return array( 
                //   'ResponseCode'        => 1, 
                //   'ResponseDesc'        => 'Failed',
                //   'ThirdPartyTransID'   => $transID
                //  );
                break;

            case 'confirm':
                return array( 
                  'ResponseCode'  => 0, 
                  'ResponseDesc'  => 'Success',
                  'ThirdPartyTransID'   => $transID
                 );
                break;

            case 'pay':
                // Remove the plus sign before the customer's phone number if present
                $phone      = $_POST['phone'];
                $amount     = $_POST['amount'];
                $reference  = $_POST['reference'] ?? rand(0, 10000);

                if ( substr( $phone, 0,1 ) == "+" ) $phone = str_replace( "+", "", $phone );
                if ( substr( $phone, 0,1 ) == "0" ) $phone = preg_replace('/^0/', '254', $phone);

                $endpoint = ( getenv( 'MPESA_ENV' ) == 'live' ) ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest' : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

                $timestamp = date( 'YmdHis' );
                $password = base64_encode( getenv( 'MPESA_SHORTCODE' ).getenv( 'MPESA_PASSKEY' ).$timestamp );


                $curl = curl_init();
                curl_setopt( $curl, CURLOPT_URL, $endpoint );
                curl_setopt( $curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization:Bearer '.$this->token ] );

                $curl_post_data = array( 
                    'BusinessShortCode' => getenv( 'MPESA_HO_NUMBER' ),
                    'Password'          => $password,
                    'Timestamp'         => $timestamp,
                    'TransactionType'   => 'CustomerPayBillOnline',
                    'Amount'            => round( $amount ),
                    'PartyA'            => $phone,
                    'PartyB'            => getenv( 'MPESA_SHORTCODE' ),
                    'PhoneNumber'       => $phone,
                    'CallBackURL'       => getenv( 'MPESA_CALLBACK_URL' ),
                    'AccountReference'  => $reference,
                    'TransactionDesc'   => 'Ijiji Payment',
                    'Remark'            => 'Ijiji Payment'
                );

                $data_string = json_encode( $curl_post_data );
                curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
                curl_setopt( $curl, CURLOPT_POST, true );
                curl_setopt( $curl, CURLOPT_POSTFIELDS, $data_string );
                curl_setopt( $curl, CURLOPT_HEADER, false );
                $response = curl_exec( $curl );
                
                return json_decode( $response );
                break;

            case 'reconcile':
                $response = json_decode( file_get_contents( 'php://input' ), true );

                if( ! isset( $response['Body'] ) ){
                    return;
                } else {
                    return MpesaPaymentsController::store();
                }
                break;

            case 'register':
                $endpoint = ( getenv( 'MPESA_ENV' ) == 'live' ) ? 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl' : 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl';

                $curl = curl_init();
                curl_setopt( $curl, CURLOPT_URL, $endpoint );
                curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json','Authorization:Bearer '.$this->token ) );
                    
                $curl_post_data = array( 
                    'ShortCode'         => getenv( 'MPESA_SHORTCODE' ),
                    'ResponseType'      => 'Cancelled',
                    'ConfirmationURL'   => getenv( 'MPESA_CONFIRMATION_URL' ),
                    'ValidationURL'     => getenv( 'MPESA_VALIDATION_URL' )
                );

                $data_string = json_encode( $curl_post_data );
                curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
                curl_setopt( $curl, CURLOPT_POST, true );
                curl_setopt( $curl, CURLOPT_POSTFIELDS, $data_string );
                curl_setopt( $curl, CURLOPT_HEADER, false );
                return json_decode( curl_exec( $curl ) );
                break;
            
            default:
                $endpoint = ( getenv( 'MPESA_ENV' ) == 'live' ) ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

                $credentials = base64_encode( getenv( 'MPESA_APP_KEY' ).':'.getenv( 'MPESA_APP_SECRET' ) );

                $curl = curl_init();
                curl_setopt( $curl, CURLOPT_URL, $endpoint );
                curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Basic '.$credentials ) );
                curl_setopt( $curl, CURLOPT_HEADER, false );
                curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
                $curl_response = curl_exec( $curl );
                
                return json_decode( $curl_response )->access_token;
                break;
        }
    }
}
