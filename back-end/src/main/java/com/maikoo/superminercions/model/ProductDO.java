package com.maikoo.superminercions.model;

import lombok.Data;

import java.math.BigDecimal;

@Data
public class ProductDO {
    private Long id;
    private String imageUri;
    private String productNumber;
    private String model;
    private String name;
    private BigDecimal performance;
    private BigDecimal price;
    private String type;
    private String createdAt;
    private String updatedAt;
    private String deletedAt;
}
