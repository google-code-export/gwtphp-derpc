<?php

require_once PHPRPC_ROOT . 'classes.php';

class RPCServletUtils {
	
	const BUFFER_SIZE = 4096;
	
	const ACCEPT_ENCODING = "HTTP_ACCEPT_ENCODING";
	
	const ATTACHMENT = "attachment";
	
	const CHARSET_UTF8 = "UTF-8";
	
	const CONTENT_DISPOSITION = "Content-Disposition";
	
	const CONTENT_ENCODING = "Content-Encoding";
	
	const CONTENT_ENCODING_GZIP = "gzip";
	
	const CONTENT_TYPE_APPLICATION_JSON_UTF8 = "application/json; charset=utf-8";
	
	const GENERIC_FAILURE_MSG = "The call failed on the server; see server log for details";
	
	const GWT_RPC_CONTENT_TYPE = "text/x-gwt-rpc";
	
	const UNCOMPRESSED_BYTE_SIZE_LIMIT = 256;
	
	public static function acceptsGzipEncoding() {
		$acceptEncoding = $_SERVER[self::ACCEPT_ENCODING];
		if (empty($acceptEncoding)) {
			return false;
		}
		
		return mb_strpos($acceptEncoding, self::CONTENT_ENCODING_GZIP) !== false;
	}
	
	public static function exceedsUncompressedContentLengthLimit(&$content) {
		return (mb_strlen($content) * 2) > self::UNCOMPRESSED_BYTE_SIZE_LIMIT;
	}
	
	public static function isExpectedException(Method $serviceIntfMethod, Throwable $cause) {
		assert(!is_null($serviceIntfMethod));
		assert(!is_null($cause));
		
		$exceptionsThrown = $serviceIntfMethod->getExceptionTypes();
		if (count($exceptionsThrown) <= 0) {
			return false;
		}
		
		$causeType = $cause->getExceptionClass();
		foreach ($exceptionsThrown as $exceptionThrown) {
			assert(!is_null($exceptionThrown));
			
			if ($exceptionThrown->isAssignableFrom($causeType)) {
				return true;
			}
		}
		return false;
	}
	
	public static function readContent($expectedContentType, $expectedCharSet) {
		if (!is_null($expectedContentType)) {
			self::checkContentTypeIgnoreCase($expectedContentType);
		}
		if (!is_null($expectedCharSet)) {
			self::checkCharacterEncodingIgnoreCase($expectedCharSet);
		}
		
		/*
	     * Need to support 'Transfer-Encoding: chunked', so do not rely on
	     * presence of a 'Content-Length' request header.
	     */
		// always UTF8
		return file_get_contents('php://input');
	}
	
	public static function readContentAsGwtRpc() {
		return self::readContent(self::GWT_RPC_CONTENT_TYPE, self::CHARSET_UTF8);
	}
	
	/**
	 * @deprecated
	 */
	public static function readContentAsUtf8($checkHeaders = null) {
		if (is_null($checkHeaders)) {
			return self::readContent(null, null);
		}
		else {
			return self::readContent(self::GWT_RPC_CONTENT_TYPE, self::CHARSET_UTF8);
		}
	}
	
	public static function setGzipEncodingHeader() {
		header(self::CONTENT_ENCODING . ': ' . self::CONTENT_ENCODING_GZIP);
	}
	
	public static function shouldGzipResponseContent($responseContent) {
		return self::acceptsGzipEncoding() && self::exceedsUncompressedContentLengthLimit($responseContent);
	}
	
	public static function writeResponse(&$responseContent, $gzipResponse) {
		if ($gzipResponse) {	
			ob_start("ob_gzhandler");
		}
		
		header('Content-type: ' . self::CONTENT_TYPE_APPLICATION_JSON_UTF8);
		header('Status: 200');
		header(self::CONTENT_DISPOSITION . ': ' . self::ATTACHMENT);
		echo $responseContent;
	}
	
	public static function writeResponseForUnexpectedFailure(Throwable $failure) {
		header('Content-type: text/plain');
		header('Status: 500');
		echo self::GENERIC_FAILURE_MSG;
	}
	
	private static function checkCharacterEncodingIgnoreCase($expectedCharSet) {
		$encodingOkay = false;
		$characterEncoding = $_SERVER['HTTP_ACCEPT_CHARSET'];
		if (!is_null($characterEncoding)) {
			if (mb_strtolower($characterEncoding)) {
				if (mb_strpos($expectedCharSet, $characterEncoding) !== false) {
					$encodingOkay = true;
				}
			}
		}
		
		if (!$encodingOkay) {
			throw new ServletException('Character Encoding is "' . 
				(is_null($characterEncoding) ? '(null)' : $characterEncoding) . 
				'". Expected "' . $expectedCharSet . '"'
			);
		}
	}
	
	private static function checkContentTypeIgnoreCase($expectedContentType) {
		$contentTypeIsOkay = false;
		if (isset($_SERVER['CONTENT_TYPE'])) {
			$contentType = mb_strtolower($_SERVER['CONTENT_TYPE']);
			$pos = mb_strpos($contentType, mb_strtolower($expectedContentType));
			if ($pos !== false && $pos == 0) {
				$contentTypeIsOkay = true;
			}
		}
		
		if (!$contentTypeIsOkay) {
			throw new ServletException(
				'Content-Type was "' .
				(is_null($contentType) ? '(null)' : $contentType) .
				'". Expected "' . $expectedContentType . '".'
			);
		}
	}
	
}
