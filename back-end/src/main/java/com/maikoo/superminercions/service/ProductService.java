package com.maikoo.superminercions.service;

import com.maikoo.superminercions.model.dto.ProductBackDTO;
import com.maikoo.superminercions.model.dto.ProductDTO;
import com.maikoo.superminercions.model.dto.ProductInformationDTO;
import com.maikoo.superminercions.model.query.ProductQuery;

import java.util.List;

public interface ProductService {

    ProductInformationDTO information(String productNumber);

    List<ProductDTO> list();

    void buy(String productNumber);

    /**
     * 新增矿机
     * @param productQuery
     */
    void addMiner(ProductQuery productQuery);

    /**
     * 矿机修改
     * @param productQuery
     */
    void updateMiner(ProductQuery productQuery);

    /**
     * 获取矿机列表
     * @return
     */
    List<ProductBackDTO> backList();




}
