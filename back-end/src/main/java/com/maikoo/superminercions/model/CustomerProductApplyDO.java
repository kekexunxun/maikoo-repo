package com.maikoo.superminercions.model;

import lombok.Data;

@Data
public class CustomerProductApplyDO extends OrderDO {
    private CustomerDO customerDO;
    private ProductDO productDO;
}
