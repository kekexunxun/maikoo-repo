package com.maikoo.businessdirectory.util;

import com.maikoo.businessdirectory.exception.SystemException;
import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.StatusLine;
import org.apache.http.client.HttpResponseException;
import org.apache.http.client.ResponseHandler;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.ContentType;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.CloseableHttpClient;
import org.apache.http.impl.client.HttpClients;

import java.io.IOException;
import java.net.URI;

public class RequestUtil {

    public static <R> R get(URI uri, ResponseService<R> responseService) {
        CloseableHttpClient httpclient = HttpClients.createDefault();
        HttpGet httpGet = new HttpGet(uri);

        try {
            return httpclient.execute(httpGet, baseResponseHandler(responseService));
        } catch (IOException e) {
            throw new SystemException("请求出现错误", e);
        }
    }

    public static <R> R postByJson(URI uri, String requestParameter, ResponseService<R> responseService) {
        CloseableHttpClient httpclient = HttpClients.createDefault();
        HttpPost httpPost = new HttpPost(uri);
        httpPost.setEntity(new StringEntity(requestParameter, ContentType.APPLICATION_JSON));

        try {
            return httpclient.execute(httpPost, baseResponseHandler(responseService));
        } catch (IOException e) {
            throw new SystemException("请求出现错误", e);
        }
    }

    public static <R> ResponseHandler<R> baseResponseHandler(ResponseService<R> responseService) throws HttpResponseException {
        return new ResponseHandler() {
            @Override
            public R handleResponse(final HttpResponse response) throws IOException {
                StatusLine statusLine = response.getStatusLine();
                HttpEntity entity = response.getEntity();
                if (statusLine.getStatusCode() >= 400) {
                    throw new HttpResponseException(
                            statusLine.getStatusCode(),
                            statusLine.getReasonPhrase());
                }
                return responseService.handle(entity.getContent());
            }
        };
    }
}
