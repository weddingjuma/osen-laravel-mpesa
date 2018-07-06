<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Mpesa extends Controller
{
	public function __invoke( Request $request, $path, $transID = 0 )
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
        
        $token = json_decode( $curl_response )->access_token;

        switch ( $path ) {
            case 'validate':
                $data = $request->getContent();

                $cq = $request->query( 'cb', null );
                if( $cq == "0" ){
                    return array( 
                      'ResponseCode'            => 0, 
                      'ResponseDesc'            => 'Success',
                      'ThirdPartyTransID'       => $transID
                     );
                } else {
                    $callback = explode( '@', $cq );
                    $class = new $callback[0];
                    $method = $callback[1];

                    if ( !call_user_func_array( array( $class, $method ), array( json_decode( $data, true)['Body'] ) )) {
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
                $cq = $request->query( 'cb', 0 );
                if( $cq == "0" ){
                    return array( 
                      'ResponseCode'            => 0, 
                      'ResponseDesc'            => 'Success',
                      'ThirdPartyTransID'       => $transID
                     );
                } else {
                    $callback = explode( '@', $cq );
                    $class = new $callback[0];
                    $method = $callback[1];

                    if ( !call_user_func_array( array( $class, $method ), array( json_decode( $data, true )['Body'] ) ) ) {
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
                $data = $request->getContent();

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
                    'CallBackURL'       => getenv('APP_URL').':'.$_SERVER['SERVER_PORT'].'/reconcile?cb='.getenv( 'MPESA_RECONCILE' ),
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
                $data = $request->getContent();
                $response = json_decode( $data, true );

                $cq = $request->query( 'cb', 0 );
                if( $cq == 0 ){
                    $class = $callback[0];
                    $method = $callback[1];
                    return array( 
                      'ResponseCode'            => 0, 
                      'ResponseDesc'            => 'Success',
                      'ThirdPartyTransID'       => $transID
                     );
                } else {

                    if( ! isset( $body['Body'] ) ){
                        return  call_user_func_array( array( $class, $method ), array( null ) );
                    } else {
                        $payment = isset( $response['Body']['stkCallback'] ) ? $response['Body']['stkCallback'] : null;
                        return  call_user_func_array( array( $class, $method ), array( $payment ) );
                    }
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
                    'ConfirmationURL'   => getenv('APP_URL').':'.$_SERVER['SERVER_PORT'].'/confirm?cb'.getenv( 'MPESA_CONFIRM' ),
                    'ValidationURL'     => getenv('APP_URL').':'.$_SERVER['SERVER_PORT'].'/validate?cb'.getenv( 'MPESA_VALIDATE' )
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
