package com.maikoo.superminercions.exception;

public class DiscordPhoneException extends RuntimeException {
    public DiscordPhoneException() {
    }

    public DiscordPhoneException(String message) {
        super(message);
    }

    public DiscordPhoneException(String message, Throwable cause) {
        super(message, cause);
    }

    public DiscordPhoneException(Throwable cause) {
        super(cause);
    }

    public DiscordPhoneException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
