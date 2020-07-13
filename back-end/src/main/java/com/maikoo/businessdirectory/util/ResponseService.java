package com.maikoo.businessdirectory.util;

import java.io.IOException;
import java.io.InputStream;

public interface ResponseService<R> {
    public R handle(InputStream content) throws IOException;
}
