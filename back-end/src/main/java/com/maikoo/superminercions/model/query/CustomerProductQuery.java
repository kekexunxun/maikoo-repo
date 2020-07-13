package com.maikoo.superminercions.model.query;

import lombok.Data;

import java.math.BigDecimal;

@Data
public class CustomerProductQuery {
    private long listSn;
    private String minerName;
    private String minerModel;
    private BigDecimal minerCountingForce;
}
