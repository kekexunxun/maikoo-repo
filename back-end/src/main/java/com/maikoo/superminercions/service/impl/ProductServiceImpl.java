package com.maikoo.superminercions.service.impl;

import com.maikoo.superminercions.dao.CustomerProductApplyDao;
import com.maikoo.superminercions.dao.CustomerProductDao;
import com.maikoo.superminercions.dao.OrderDao;
import com.maikoo.superminercions.dao.ProductDao;
import com.maikoo.superminercions.exception.GetMinerDetailException;
import com.maikoo.superminercions.exception.GetMinerListException;
import com.maikoo.superminercions.exception.InvalidParameterException;
import com.maikoo.superminercions.model.CustomerDO;
import com.maikoo.superminercions.model.CustomerProductApplyDO;
import com.maikoo.superminercions.model.CustomerProductDO;
import com.maikoo.superminercions.model.ProductDO;
import com.maikoo.superminercions.model.dto.ProductBackDTO;
import com.maikoo.superminercions.model.dto.ProductDTO;
import com.maikoo.superminercions.model.dto.ProductInformationDTO;
import com.maikoo.superminercions.model.query.ProductQuery;
import com.maikoo.superminercions.service.ProductService;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.SerialNumberUtil;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.dao.DuplicateKeyException;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import javax.servlet.http.HttpSession;
import java.util.ArrayList;
import java.util.List;
import java.util.UUID;

@Service
public class ProductServiceImpl implements ProductService {
    @Autowired
    private ProductDao productDao;
    @Autowired
    private CustomerProductDao customerProductDao;
    @Autowired
    private CustomerProductApplyDao customerProductApplyDao;
    @Autowired
    private OrderDao orderDao;
    @Autowired
    private HttpSession session;

    @Override
    public ProductInformationDTO information(String productNumber) {
        ProductInformationDTO productInformationDTO = null;

        ProductDO productDO = null;
        try {
            productDO = productDao.selectOneByProductNumber(productNumber);
            if (productDO != null) {
                productInformationDTO = ProductInformationDTO.valueOf(productDO);
            }
        } catch (Exception e) {
            throw new GetMinerDetailException("矿机详情获取失败");
        }
        return productInformationDTO;
    }

    @Override
    public List<ProductDTO> list() {
        List<ProductDTO> productDTOList = new ArrayList<>();
        List<ProductDO> productDOList = null;
        try {
            productDOList = productDao.selectAll();
            if (productDOList != null && productDOList.size() > 0) {
                productDOList.forEach(productDO -> productDTOList.add(ProductDTO.valueOf(productDO)));
            }
        } catch (Exception e) {
            throw  new GetMinerListException("获取矿机列表失败");
        }
        return productDTOList;
    }

    @Override
    @Transactional
    public void buy(String productNumber) {
        ProductDO productDO = null;
        try {
            productDO = productDao.selectOneByProductNumber(productNumber);
        } catch (Exception e) {
            throw new InvalidParameterException("无效的产品编号");
        }
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        CustomerProductApplyDO customerProductApplyDO = new CustomerProductApplyDO();
        customerProductApplyDO.setOrderSN(SerialNumberUtil.order());
        customerProductApplyDO.setCustomerDO(currentCustomerDO);
        customerProductApplyDO.setStatus(ConstantUtil.ORDER_STATUS_SMC_TRADING_PROCESSING);
        customerProductApplyDO.setCustomerDO(currentCustomerDO);
        customerProductApplyDO.setProductDO(productDO);

        createdCommonOrder(customerProductApplyDO);
        customerProductApplyDao.insert(customerProductApplyDO);
    }

    @Override
    public void addMiner(ProductQuery productQuery) {
        UUID uuid = UUID.randomUUID();
        ProductDO productDO = new ProductDO();
        productDO.setName(productQuery.getMinerName());
        productDO.setPerformance(productQuery.getMinerCountForce());
        productDO.setModel(productQuery.getMinerModel());
        productDO.setPrice(productQuery.getMinerPrice());
        productDO.setProductNumber(uuid.toString().replaceAll("-", "").substring(0, 15));
        try {
            productDao.addMiner(productDO);
        } catch (DuplicateKeyException e) {
            productDO = null;
            addMiner(productQuery);
        }
    }

    @Override
    @Transactional
    public void updateMiner(ProductQuery productQuery) {
        ProductDO productDO = productDao.selectOne(productQuery.getMinerSn());
        if(productDO == null){
            throw new InvalidParameterException();
        }

        productDO.setPerformance(productQuery.getMinerCountForce());
        productDO.setPrice(productQuery.getMinerPrice());
        productDao.update(productDO);

        CustomerProductDO customerProductDO = new CustomerProductDO();
        customerProductDO.setProductNumber(productDO.getProductNumber());
        customerProductDO.setPerformance(productDO.getPerformance());
        customerProductDao.updatePerformance(customerProductDO);
    }

    @Override
    public List<ProductBackDTO> backList() {
        List<ProductBackDTO> productBackDTO = new ArrayList<>();
        try {
            List<ProductDO> productDOList = productDao.selectAll();
            if (productDOList != null && productDOList.size() > 0) {
                productDOList.forEach(productDO -> productBackDTO.add(ProductBackDTO.valueOf(productDO)));
            }
        } catch (Exception e) {
            new GetMinerListException("矿机列表获取失败");
        }
        return productBackDTO;
    }

    private void createdCommonOrder(CustomerProductApplyDO customerProductApplyDO) {
        try{
            orderDao.insert(customerProductApplyDO);
        }catch (DuplicateKeyException e){
            customerProductApplyDO.setOrderSN(SerialNumberUtil.order());
            createdCommonOrder(customerProductApplyDO);
        }
    }
}
