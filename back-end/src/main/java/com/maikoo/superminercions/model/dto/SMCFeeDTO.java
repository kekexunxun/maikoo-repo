package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonInclude;
import lombok.Data;

@Data
@JsonInclude(JsonInclude.Include.NON_NULL)
public class SMCFeeDTO {
    private Float smc2rmbRate;
    private Float smcExtractRate;
}
