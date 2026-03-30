<?php

namespace Mk4U\Http;

enum Status: int
{
        //1xx: Informational - Request received, continuing process
    case Continue                      = 100; //[RFC-ietf-httpbis-semantics, Section 15.2.1]
    case SwitchingProtocols            = 101; //[RFC-ietf-httpbis-semantics, Section 15.2.2]
    case Processing                    = 102; //[RFC2518]
    case EarlyHints                    = 103; //[RFC8297]
        //104-199 Unassigned

        //2xx: Success - The action was successfully received, understood, and accepted
    case Ok                            = 200; //[RFC-ietf-httpbis-semantics, Section 15.3.1] 
    case Created                       = 201; //[RFC-ietf-httpbis-semantics, Section 15.3.2] 
    case Accepted                      = 202; //[RFC-ietf-httpbis-semantics, Section 15.3.3] 
    case NonAuthoritativeInformation   = 203; //[RFC-ietf-httpbis-semantics, Section 15.3.4] 
    case NoContent                     = 204; //[RFC-ietf-httpbis-semantics, Section 15.3.5] 
    case ResetContent                  = 205; //[RFC-ietf-httpbis-semantics, Section 15.3.6] 
    case PartialContent                = 206; //[RFC-ietf-httpbis-semantics, Section 15.3.7] 
    case MultiStatus                   = 207; //[RFC4918] 
    case AlreadyReported               = 208; //[RFC5842] 
        //209-225 Unassigned
    case ImUsed                        = 226; //[RFC3229] 
        //227-299 Unassigned

        //3xx: Redirection - Further action must be taken in order to complete the request
    case MultipleChoices               = 300; //[RFC-ietf-httpbis-semantics, Section 15.4.1]
    case MovedPermanently              = 301; //[RFC-ietf-httpbis-semantics, Section 15.4.2]
    case Found                         = 302; //[RFC-ietf-httpbis-semantics, Section 15.4.3]
    case SeeOther                      = 303; //[RFC-ietf-httpbis-semantics, Section 15.4.4]
    case NotModified                   = 304; //[RFC-ietf-httpbis-semantics, Section 15.4.5]
    case UseProxy                      = 305; //[RFC-ietf-httpbis-semantics, Section 15.4.6]
        //306 (Unused) [RFC-ietf-httpbis-semantics, Section 15.4.7]
    case TemporaryRedirect             = 307; //[RFC-ietf-httpbis-semantics, Section 15.4.8]
    case PermanentRedirect             = 308; //[RFC-ietf-httpbis-semantics, Section 15.4.9]
        //309-399 Unassigned

        //4xx: Client Error - The request contains bad syntax or cannot be fulfilled
    case BadRequest                    = 400; //[RFC-ietf-httpbis-semantics, Section 15.5.1]
    case Unauthorized                  = 401; //[RFC-ietf-httpbis-semantics, Section 15.5.2]
    case PaymentRequired               = 402; //[RFC-ietf-httpbis-semantics, Section 15.5.3]
    case Forbidden                     = 403; //[RFC-ietf-httpbis-semantics, Section 15.5.4]
    case NotFound                      = 404; //[RFC-ietf-httpbis-semantics, Section 15.5.5]
    case MethodNotAllowed              = 405; //[RFC-ietf-httpbis-semantics, Section 15.5.6]
    case NotAcceptable                 = 406; //[RFC-ietf-httpbis-semantics, Section 15.5.7]
    case ProxyAuthenticationRequired   = 407; //[RFC-ietf-httpbis-semantics, Section 15.5.8]
    case RequestTimeout                = 408; //[RFC-ietf-httpbis-semantics, Section 15.5.9]
    case Conflict                      = 409; //[RFC-ietf-httpbis-semantics, Section 15.5.10]
    case Gone                          = 410; //[RFC-ietf-httpbis-semantics, Section 15.5.11]
    case LengthRequired                = 411; //[RFC-ietf-httpbis-semantics, Section 15.5.12]
    case PreconditionFailed            = 412; //[RFC-ietf-httpbis-semantics, Section 15.5.13]
    case ContentTooLarge               = 413; //[RFC-ietf-httpbis-semantics, Section 15.5.14]
    case URITooLong                    = 414; //[RFC-ietf-httpbis-semantics, Section 15.5.15]
    case UnsupportedMediaType          = 415; //[RFC-ietf-httpbis-semantics, Section 15.5.16]
    case RangeNotSatisfiable           = 416; //[RFC-ietf-httpbis-semantics, Section 15.5.17]
    case ExpectationFailed             = 417; //[RFC-ietf-httpbis-semantics, Section 15.5.18]
        //418 (Unused) [RFC-ietf-httpbis-semantics, Section 15.5.19]
        //419-420 Unassigned
    case MisdirectedRequest            = 421; //[RFC-ietf-httpbis-semantics, Section 15.5.20]
    case UnprocessableContent          = 422; //[RFC-ietf-httpbis-semantics, Section 15.5.21]
    case Locked                        = 423; //[RFC4918]
    case FailedDependency              = 424; //[RFC4918]
    case TooEarly                      = 425; //[RFC8470]
    case UpgradeRequired               = 426; //[RFC-ietf-httpbis-semantics, Section 15.5.22]
        //427 Unassigned
    case PreconditionRequired          = 428; //[RFC6585]
    case TooManyRequests               = 429; //[RFC6585]
        //430 Unassigned
    case RequestHeaderFieldsTooLarge   = 431; //[RFC6585]
        //432-450 Unassigned
    case UnavailableForLegalReasons    = 451; //[RFC7725]
        //452-499 Unassigned

        //5xx: Server Error - The server failed to fulfill an apparently valid request
    case InternalServerError           = 500; //[RFC-ietf-httpbis-semantics, Section 15.6.1]
    case NotImplemented                = 501; //[RFC-ietf-httpbis-semantics, Section 15.6.2]
    case BadGateway                    = 502; //[RFC-ietf-httpbis-semantics, Section 15.6.3]
    case ServiceUnavailable            = 503; //[RFC-ietf-httpbis-semantics, Section 15.6.4]
    case GatewayTimeout                = 504; //[RFC-ietf-httpbis-semantics, Section 15.6.5]
    case HTTPVersionNotSupported       = 505; //[RFC-ietf-httpbis-semantics, Section 15.6.6]
    case VariantAlsoNegotiates         = 506; //[RFC2295]
    case InsufficientStorage           = 507; //[RFC4918]
    case LoopDetected                  = 508; //[RFC5842]
        //509 Unassigned
    case NotExtended                   = 510;                    //(OBSOLETED) - [RFC2774][status-change-http-experiments-to-historic]
    case NetworkAuthenticationRequired = 511;  //[RFC6585]
    //512-599 Unassigned

    /**
     * Obtiene la frase para un codigo de estado en especifico
     */
    public function message(): string
    {
        return match ($this) {
            self::Continue                      => 'Continue',
            self::SwitchingProtocols            => 'Switching Protocols',
            self::Processing                    => 'Processing',
            self::EarlyHints                    => 'Early Hints',
            self::Ok                            => 'Ok',
            self::Created                       => 'Created',
            self::Accepted                      => 'Accepted',
            self::NonAuthoritativeInformation   => 'Non Authoritative Information',
            self::NoContent                     => 'No Content',
            self::ResetContent                  => 'Reset Content',
            self::PartialContent                => 'Partial Content',
            self::MultiStatus                   => 'Multi Status',
            self::AlreadyReported               => 'Already Reported',
            self::ImUsed                        => 'Im Used',
            self::MultipleChoices               => 'Multiple Choices',
            self::MovedPermanently              => 'Moved Permanently',
            self::Found                         => 'Found',
            self::SeeOther                      => 'See Other',
            self::NotModified                   => 'Not Modified',
            self::UseProxy                      => 'Use Proxy',
            self::TemporaryRedirect              => 'Temporary Redirect',
            self::PermanentRedirect              => 'Permanent Redirect',
            self::BadRequest                     => 'Bad Request',
            self::Unauthorized                   => 'Unauthorized',
            self::PaymentRequired                => 'Payment Required',
            self::Forbidden                      => 'Forbidden',
            self::NotFound                       => 'Not Found',
            self::MethodNotAllowed               => 'Method Not Allowed',
            self::NotAcceptable                  => 'Not Acceptable',
            self::ProxyAuthenticationRequired    => 'Proxy Authentication Required',
            self::RequestTimeout                 => 'Request Timeout',
            self::Conflict                       => 'Conflict',
            self::Gone                           => 'Gone',
            self::LengthRequired                 => 'Length Required',
            self::PreconditionFailed             => 'Precondition Failed',
            self::ContentTooLarge                => 'Content Too Large',
            self::URITooLong                     => 'URI Too Long',
            self::UnsupportedMediaType           => 'Unsupported Media Type',
            self::RangeNotSatisfiable            => 'Range Not Satisfiable',
            self::ExpectationFailed              => 'Expectation Failed',
            self::MisdirectedRequest             => 'Misdirected Request',
            self::UnprocessableContent           => 'Unprocessable Content',
            self::Locked                         => 'Locked',
            self::FailedDependency               => 'Failed Dependency',
            self::TooEarly                       => 'Too Early',
            self::UpgradeRequired                => 'Upgrade Required',
            self::PreconditionRequired           => 'Precondition Required',
            self::TooManyRequests                => 'Too Many Requests',
            self::RequestHeaderFieldsTooLarge    => 'Request Header Fields Too Large',
            self::UnavailableForLegalReasons     => 'Unavailable For Legal Reasons',
            self::InternalServerError            => 'Internal Server Error',
            self::NotImplemented                 => 'Not Implemented',
            self::BadGateway                     => 'Bad Gateway',
            self::ServiceUnavailable             => 'Service Unavailable',
            self::GatewayTimeout                 => 'Gateway Timeout',
            self::HTTPVersionNotSupported        => 'HTTP Version Not Supported',
            self::VariantAlsoNegotiates          => 'Variant Also Negotiates',
            self::InsufficientStorage            => 'Insufficient Storage',
            self::LoopDetected                   => 'Loop Detected',
            self::NotExtended                    => 'Not Extended',
            self::NetworkAuthenticationRequired  => 'Network Authentication Required',
        };
    }

    /**
     * Devuelve la frase en dependencia del codigo pasado
     */
    public static function phrase(int $code): string
    {
        return match ($code) {
            self::Continue->value                      => self::Continue->message(),
            self::SwitchingProtocols->value            => self::SwitchingProtocols->message(),
            self::Processing->value                    => self::Processing->message(),
            self::EarlyHints->value                    => self::EarlyHints->message(),
            self::Ok->value                            => self::Ok->message(),
            self::Created->value                       => self::Created->message(),
            self::Accepted->value                      => self::Accepted->message(),
            self::NonAuthoritativeInformation->value   => self::NonAuthoritativeInformation->message(),
            self::NoContent->value                     => self::NoContent->message(),
            self::ResetContent->value                  => self::ResetContent->message(),
            self::PartialContent->value                => self::PartialContent->message(),
            self::MultiStatus->value                   => self::MultiStatus->message(),
            self::AlreadyReported->value               => self::AlreadyReported->message(),
            self::ImUsed->value                        => self::ImUsed->message(),
            self::MultipleChoices->value               => self::MultipleChoices->message(),
            self::MovedPermanently->value              => self::MovedPermanently->message(),
            self::Found->value                         => self::Found->message(),
            self::SeeOther->value                      => self::SeeOther->message(),
            self::NotModified->value                   => self::NotModified->message(),
            self::UseProxy->value                      => self::UseProxy->message(),
            self::TemporaryRedirect->value             => self::TemporaryRedirect->message(),
            self::PermanentRedirect->value             => self::PermanentRedirect->message(),
            self::BadRequest->value                    => self::BadRequest->message(),
            self::Unauthorized->value                  => self::Unauthorized->message(),
            self::PaymentRequired->value               => self::PaymentRequired->message(),
            self::Forbidden->value                     => self::Forbidden->message(),
            self::NotFound->value                      => self::NotFound->message(),
            self::MethodNotAllowed->value              => self::MethodNotAllowed->message(),
            self::NotAcceptable->value                 => self::NotAcceptable->message(),
            self::ProxyAuthenticationRequired->value   => self::ProxyAuthenticationRequired->message(),
            self::RequestTimeout->value                => self::RequestTimeout->message(),
            self::Conflict->value                      => self::Conflict->message(),
            self::Gone->value                          => self::Gone->message(),
            self::LengthRequired->value                => self::LengthRequired->message(),
            self::PreconditionFailed->value            => self::PreconditionFailed->message(),
            self::ContentTooLarge->value               => self::ContentTooLarge->message(),
            self::URITooLong->value                    => self::URITooLong->message(),
            self::UnsupportedMediaType->value          => self::UnsupportedMediaType->message(),
            self::RangeNotSatisfiable->value           => self::RangeNotSatisfiable->message(),
            self::ExpectationFailed->value             => self::ExpectationFailed->message(),
            self::MisdirectedRequest->value            => self::MisdirectedRequest->message(),
            self::UnprocessableContent->value          => self::UnprocessableContent->message(),
            self::Locked->value                        => self::Locked->message(),
            self::FailedDependency->value              => self::FailedDependency->message(),
            self::TooEarly->value                      => self::TooEarly->message(),
            self::UpgradeRequired->value               => self::UpgradeRequired->message(),
            self::PreconditionRequired->value          => self::PreconditionRequired->message(),
            self::TooManyRequests->value               => self::TooManyRequests->message(),
            self::RequestHeaderFieldsTooLarge->value   => self::RequestHeaderFieldsTooLarge->message(),
            self::UnavailableForLegalReasons->value    => self::UnavailableForLegalReasons->message(),
            self::InternalServerError->value           => self::InternalServerError->message(),
            self::NotImplemented->value                => self::NotImplemented->message(),
            self::BadGateway->value                    => self::BadGateway->message(),
            self::ServiceUnavailable->value            => self::ServiceUnavailable->message(),
            self::GatewayTimeout->value                => self::GatewayTimeout->message(),
            self::HTTPVersionNotSupported->value       => self::HTTPVersionNotSupported->message(),
            self::VariantAlsoNegotiates->value         => self::VariantAlsoNegotiates->message(),
            self::InsufficientStorage->value           => self::InsufficientStorage->message(),
            self::LoopDetected->value                  => self::LoopDetected->message(),
            self::NotExtended->value                   => self::NotExtended->message(),
            self::NetworkAuthenticationRequired->value => self::NetworkAuthenticationRequired->message(),
            default => throw new \InvalidArgumentException("Invalid status code arguments")
            
        };
    }
}
