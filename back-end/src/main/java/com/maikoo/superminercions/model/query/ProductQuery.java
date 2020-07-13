package com.maikoo.superminercions.model.query;

import lombok.Data;

import java.math.BigDecimal;

@Data
public class ProductQuery {
    //矿机编号
    private long minerSn;
    //矿机名称
    private String minerName;
    //矿机的计算能力
    private BigDecimal minerCountForce;
    //矿机的模型
    private String minerModel;
    //矿机的价格
    private BigDecimal minerPrice;



}
