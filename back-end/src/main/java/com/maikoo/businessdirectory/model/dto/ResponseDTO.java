package com.maikoo.businessdirectory.model.dto;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonView;
import lombok.Data;

@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class ResponseDTO<T> {
    @JsonView(Object.class)
    private int code;
    @JsonView(Object.class)
    private String msg;
    @JsonView(Object.class)
    private T data;

    public ResponseDTO(int code, String msg) {
        this.code = code;
        this.msg = msg;
    }

    public ResponseDTO(int code, String msg, T data) {
        this.code = code;
        this.msg = msg;
        this.data = data;
    }
}
