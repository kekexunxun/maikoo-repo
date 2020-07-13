package com.maikoo.superminercions.model;

import lombok.Data;

import java.math.BigDecimal;

@Data
public class ETHWithdrawalDO extends OrderDO{
    private String walletAddress;
    private BigDecimal quantity;
}
