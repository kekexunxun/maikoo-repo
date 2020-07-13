package com.maikoo.superminercions.model;

import lombok.Data;

import java.math.BigDecimal;

@Data
public class CustomerProductDO {
    private Long id;
    private String userProductSN;
    private CustomerDO customerDO;
    private String imageUri;
    private String productNumber;
    private String model;
    private String name;
    private BigDecimal performance;
    private BigDecimal output;
    private boolean isDisable;
    private Long createdAt;
}
