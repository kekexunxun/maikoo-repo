package com.maikoo.superminercions.service;

public interface SMCPriceService {
    /**
     * 用于设置今天SMC价格
     * 如果今天SMC价格未被设置的时候，
     * 将今天SMC价格设置成昨天SMC价格
     */
    void updateTodaySMCPrice();
}
