package com.maikoo.businessdirectory.model.query;

import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.Data;

@Data
public class FormIdQuery {
    @JsonProperty("formId")
    private String formId;
    @JsonProperty("expireAt")
    private long expireAt;
}
