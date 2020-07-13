package com.maikoo.superminercions.exception;

public class GetTradingRecordDetailException extends RuntimeException{

        public GetTradingRecordDetailException() {
        }

        public GetTradingRecordDetailException(String message) {
            super(message);
        }

        public GetTradingRecordDetailException(String message, Throwable cause) {
            super(message, cause);
        }

        public GetTradingRecordDetailException(Throwable cause) {
            super(cause);
        }

        public GetTradingRecordDetailException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
            super(message, cause, enableSuppression, writableStackTrace);
        }
}
