<?php
/*
* David Kruger
* me (at) krugerdavid (dot) com
* 20/Oct/2022
*/
class BilleteraPersonal 
{
	const API_BASE_URL = 'https://www.personal.com.py/ApiComerciosMaven/webresources/';
	
	private $user;
	private $pass;

	public function __construct()
	{
		$this->user = '';
		$this->pass = '';
	}	

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function autenticacion() 
	{
		try {
			$params = [
				'usuario' => $this->user,
				'clave' => $this->pass,
			];

			$result = self::request(
				'autenticacion',
				$params
			);	

			$result = self::responseJson($result);
			self::checkErrors($result);

			return $result;
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * @param $payment_id
	 * @param $phone
	 * @param $amount
	 * @return mixed
	 * @throws \Exception
	 */
	public function pago($payment_id, $phone, $amount)
	{
		try {
			$params = [
				"idTransaccionComercio" => $payment_id,
				"lineaUsuario" => $phone,
				"monto" => $amount,
				"tokenSession" => $this->autenticacion()->mensaje
			];

			$result = $this->request(
				'pago',
				$params
			);

			$result = self::responseJson($result);
			self::checkErrors($result);

			return $result;
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * @param  $payment_id
	 * @return mixed
	 * @throws \Exception
	 */
	public function consulta($payment_id)
	{
		try {
			$params = [
				"idTransaccionComercio" => $payment_id,
				"tokenSession" => $this->autenticacion()->mensaje
			];
			$result = $this->request(
				'consulta',
				$params
			);	

			$result = self::responseJson($result);
			self::checkErrors($result);

			return $result;
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * @param  $payment_id
	 * @return mixed
	 */
	public function anulacion($payment_id)
	{
		try {
			$params = [
				"idTransaccionComercio" => $payment_id,
				"tokenSession" => $this->autenticacion()->mensaje,
				"motivo" => "solicitud de anulacion"
			];

			$result = self::request(
				'reversa',
				$params
			);	

			$result = self::responseJson($result);
			self::checkErrors($result);

			return $result;
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * @param  $action
	 * @param  $data
	 * @param  $method by default post
	 * @return mixed
	 */
	private static function request($action, $data, $method = "POST")
	{
		$data = @http_build_query($data);
		$headers = array(
			'Content-Type: application/x-www-form-urlencoded'
		);

		// enlarge time execution of php script self to infinity
		set_time_limit(0);

		$session = curl_init(self::API_BASE_URL . $action);

		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
		if($method !== 'POST')
			curl_setopt($session, CURLOPT_CUSTOMREQUEST, $method);
		else
			curl_setopt($session, CURLOPT_POST, true);

		curl_setopt($session, CURLOPT_POSTFIELDS, $data);
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_TIMEOUT, 50000);

	    $response = curl_exec($session);
	    $error = curl_error($session);
	    curl_close($session);

	    if($response === false){
	    	throw new Exception("No se pudo enviar la petición {$action}. {$error}");
	    }else{
	    	return $response;
	    }
	}

	/**
	 * @param  $result
	 * @return Exception
	 */
	private static function checkErrors($result)
	{
		$code = $result->codigo ?? $result->codigoTransaccion;
		if($code !== 0){
			$message = $result->mensaje ?? $result->mensajeTransaccion ?? $result->estado;
			throw new \Exception($message);
		}
	}

    /**
     * @param  $response
     * @return mixed
     */
    private static function responseJson($response)
    {
    	return @json_decode($response);
    }
}
