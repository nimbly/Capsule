<?php

namespace Nimbly\Capsule;

class ResponseStatus
{
	const CONTINUE = 100;
	const SWITCHING_PROTOCOLS = 101;
	const PROCESSING = 102;
	const EARLY_HINTS = 103;

	const OK = 200;
	const CREATED = 201;
	const ACCEPTED = 202;
	const NON_AUTHORITATIVE_INFORMATION = 203;
	const NO_CONTENT = 204;
	const RESET_CONTENT = 205;
	const PARTIAL_CONTENT = 206;
	const MULTI_STATUS = 207;
	const ALREADY_REPORTED = 208;
	const IM_USED = 226;

	const MULTIPLE_CHOICES = 300;
	const MOVED_PERMENENTLY = 301;
	const FOUND = 302;
	const SEE_OTHER = 303;
	const NOT_MODIFIED = 304;
	const USE_PROXY = 305;
	const SWITCH_PROXY = 306;
	const TEMPORARY_REDIRECT = 307;
	const PERMANENT_REDIRECT = 308;

	const BAD_REQUEST = 400;
	const UNAUTHORIZED = 401;
	const PAYMENT_REQUIRED = 402;
	const FORBIDDEN = 403;
	const NOT_FOUND = 404;
	const METHOD_NOT_ALLOWED = 405;
	const NOT_ACCEPTABLE = 406;
	const PROXY_AUTHENTICATION_REQUIRED = 407;
	const REQUEST_TIMEOUT = 408;
	const CONFLICT = 409;
	const GONE = 410;
	const LENGTH_REQUIRED = 411;
	const PRECONDITION_FAILED = 412;
	const PAYLOAD_TOO_LARGE = 413;
	const URI_TOO_LONG = 414;
	const UNSUPPORTED_MEDIA_TYPE = 415;
	const RANGE_NOT_SATISFIABLE = 416;
	const EXPECTATION_FAILED = 417;
	const IM_A_TEAPOT = 418;
	const MISDIRECTED_REQUEST = 421;
	const UNPROCESSABLE_ENTITY = 422;
	const LOCKED = 423;
	const FAILED_DEPENDENCY = 424;
	const TOO_EARLY = 425;
	const UPGRADE_REQUIRED = 426;
	const PRECONDITION_REQUIRED = 428;
	const TOO_MANY_REQUESTS = 429;
	const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
	const UNAVAILBLE_FOR_LEGAL_REASONS = 451;

	const INTERNAL_SERVER_ERROR = 500;
	const NOT_IMPLEMENTED = 501;
	const BAD_GATEWAY = 502;
	const SERVICE_UNAVAILABLE = 503;
	const GATEWAY_TIMEOUT = 504;
	const HTTP_VERSION_NOT_SUPPORTED = 505;
	const VARIANT_ALSO_NEGOTIATES = 506;
	const INSUFFICIENT_STORAGE = 507;
	const LOOP_DETECTED = 508;
	const NOT_EXTENDED = 510;
	const NETWORK_AUTHENTICATION_REQUIRED = 511;

	/**
	 * Official HTTP status codes mapped to their response phrase.
	 *
	 * @var array<int,string>
	 */
	protected static array $phrases = [
		self::CONTINUE => "Continue",
		self::SWITCHING_PROTOCOLS => "Switching Protocols",
		self::PROCESSING => "Processing",
		self::EARLY_HINTS => "Early Hints",

		self::OK => "Ok",
		self::CREATED => "Created",
		self::ACCEPTED => "Accepted",
		self::NON_AUTHORITATIVE_INFORMATION => "Non-Authoritative Information",
		self::NO_CONTENT => "No Content",
		self::RESET_CONTENT => "Reset Content",
		self::PARTIAL_CONTENT => "Partial Content",
		self::MULTI_STATUS => "Multi-Status",
		self::ALREADY_REPORTED => "Already Reported",
		self::IM_USED => "IM Used",

		self::MULTIPLE_CHOICES => "Multiple Choices",
		self::MOVED_PERMENENTLY => "Moved Permanently",
		self::FOUND => "Found",
		self::SEE_OTHER => "See Other",
		self::NOT_MODIFIED => "Not Modified",
		self::USE_PROXY => "Use Proxy",
		self::SWITCH_PROXY => "Switch Proxy",
		self::TEMPORARY_REDIRECT => "Temporary Redirect",
		self::PERMANENT_REDIRECT => "Permanent Redirect",

		self::BAD_REQUEST => "Bad Request",
		self::UNAUTHORIZED => "Unauthorized",
		self::PAYMENT_REQUIRED => "Payment Required",
		self::FORBIDDEN => "Forbidden",
		self::NOT_FOUND => "Not Found",
		self::METHOD_NOT_ALLOWED => "Method Not Allowed",
		self::NOT_ACCEPTABLE => "Not Acceptable",
		self::PROXY_AUTHENTICATION_REQUIRED => "Proxy Authentication Required",
		self::REQUEST_TIMEOUT => "Request Timeout",
		self::CONFLICT => "Conflict",
		self::GONE => "Gone",
		self::LENGTH_REQUIRED => "Length Required",
		self::PRECONDITION_FAILED => "Precondition Failed",
		self::PAYLOAD_TOO_LARGE => "Payload Too Large",
		self::URI_TOO_LONG => "URI Too Long",
		self::UNSUPPORTED_MEDIA_TYPE => "Unsupported Media Type",
		self::RANGE_NOT_SATISFIABLE => "Range Not Satisfiable",
		self::EXPECTATION_FAILED => "Expection Failed",
		self::IM_A_TEAPOT => "I'm A Teapot",
		self::MISDIRECTED_REQUEST => "Misdirected Request",
		self::UNPROCESSABLE_ENTITY => "Unprocessable Entity",
		self::LOCKED => "Locked",
		self::FAILED_DEPENDENCY => "Failed Dependency",
		self::TOO_EARLY => "Too Early",
		self::UPGRADE_REQUIRED => "Upgrade Required",
		self::PRECONDITION_REQUIRED => "Precondition Required",
		self::TOO_MANY_REQUESTS => "Too Many Requests",
		self::REQUEST_HEADER_FIELDS_TOO_LARGE => "Request Header Fields Too Large",
		self::UNAVAILBLE_FOR_LEGAL_REASONS => "Unavailable For Legal Reasons",

		self::INTERNAL_SERVER_ERROR => "Internal Server Error",
		self::NOT_IMPLEMENTED => "Not Implemented",
		self::BAD_GATEWAY => "Bad Gateway",
		self::SERVICE_UNAVAILABLE => "Service Unavailable",
		self::GATEWAY_TIMEOUT => "Gateway Timeout",
		self::HTTP_VERSION_NOT_SUPPORTED => "HTTP Version Not Supported",
		self::VARIANT_ALSO_NEGOTIATES => "Variant Also Negotiates",
		self::INSUFFICIENT_STORAGE => "Insufficient Storage",
		self::LOOP_DETECTED => "Loop Detected",
		self::NOT_EXTENDED => "Not Extended",
		self::NETWORK_AUTHENTICATION_REQUIRED => "Network Authentication Required"
	];

	/**
	 * Get the HTTP phrase for the response.
	 *
	 * @param int $status_code The HTTP status code to get the default reason phrase for.
	 * @return string
	 */
	public static function getPhrase(int $status_code): string
	{
		return self::$phrases[$status_code] ?? "";
	}
}