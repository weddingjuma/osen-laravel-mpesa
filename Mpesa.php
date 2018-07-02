<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Mpesa extends Controller
{
	public function __invoke( Request $request, $path, $transID = 0 )
    {

        // $endpoint = ( getenv( 'MPESA_ENV' ) == 'live' ) ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        // $credentials = base64_encode( getenv( 'MPESA_APP_KEY' ).':'.getenv( 'MPESA_APP_SECRET' ) );

        // $curl = curl_init();
        // curl_setopt( $curl, CURLOPT_URL, $endpoint );
        // curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Basic '.$credentials ) );
        // curl_setopt( $curl, CURLOPT_HEADER, false );
        // curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        // curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
        // $curl_response = curl_exec( $curl );
        
        // $token = json_decode( $curl_response )->access_token;

        switch ( $path ) {
            case 'validate':
                $class = $request->query( 'class', null );
                $method = $request->query( 'method', null );
                if( is_null( $class ) ){
                    return array( 
                      'ResponseCode'            => 0, 
                      'ResponseDesc'            => 'Success',
                      'ThirdPartyTransID'       => $transID
                     );
                } else {
                    if ( !call_user_func_array( array( $class, $method ), array( $transID ) )) {
                        return array( 
                          'ResponseCode'        => 1, 
                          'ResponseDesc'        => 'Failed',
                          'ThirdPartyTransID'   => $transID
                         );
                    } else {
                    return array( 
                      'ResponseCode'            => 0, 
                      'ResponseDesc'            => 'Success',
                      'ThirdPartyTransID'       => $transID
                     );
                    }
                }
                break;

            case 'confirm':
                $class = $request->query( 'class', null );
                $method = $request->query( 'method', null );
                if( is_null( $class ) ){
                    return array( 
                      'ResponseCode'            => 0, 
                      'ResponseDesc'            => 'Success',
                      'ThirdPartyTransID'       => $transID
                     );
                } else {
                    if ( ! call_user_func_array( array( $class, $method ), array( $transID ) )) {
                        return array( 
                          'ResponseCode'        => 1, 
                          'ResponseDesc'        => 'Failed',
                          'ThirdPartyTransID'   => $transID
                         );
                    } else {
                    return array( 
                      'ResponseCode'            => 0, 
                      'ResponseDesc'            => 'Success',
                      'ThirdPartyTransID'       => $transID
                     );
                    }
                }
                break;

            case 'pay':
                $phone      = $request->input('phone', '0705459494');
                $amount     = $request->input('amount', 1);
                $reference  = $request->input('reference', 'OSEN');

                $phone = str_replace( "+", "", $phone );
                $phone = preg_replace('/^0/', '254', $phone);

                $endpoint = ( getenv( 'MPESA_ENV' ) == 'live' ) ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest' : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

                $timestamp = date( 'YmdHis' );
                $password = base64_encode( getenv( 'MPESA_SHORTCODE' ).getenv( 'MPESA_PASSKEY' ).$timestamp );


                $curl = curl_init();
                curl_setopt( $curl, CURLOPT_URL, $endpoint );
                curl_setopt( $curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization:Bearer '.$token ] );

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
                $response = $request->getBody();
                $controller = $request->query('controller');
                $method = $requets->query('method');

                if( ! isset( $response['Body'] ) ){
                    return call_user_func_array( array( $controller, $method ), array() );
                } else {
                    return call_user_func_array( array( $controller, $method ), $response );
                }
                break;

            case 'register':
                $endpoint = ( getenv( 'MPESA_ENV' ) == 'live' ) ? 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl' : 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl';

                $curl = curl_init();
                curl_setopt( $curl, CURLOPT_URL, $endpoint );
                curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json','Authorization:Bearer '.$token ) );
                    
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
                return json_decode( curl_exec( $curl ), true );
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
