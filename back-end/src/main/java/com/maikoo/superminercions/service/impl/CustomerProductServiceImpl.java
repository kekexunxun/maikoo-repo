package com.maikoo.superminercions.service.impl;

import com.maikoo.superminercions.dao.CustomerDao;
import com.maikoo.superminercions.dao.CustomerProductDao;
import com.maikoo.superminercions.model.CustomerProductDO;
import com.maikoo.superminercions.service.CustomerProductService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.util.CollectionUtils;

import java.util.List;

@Service
public class CustomerProductServiceImpl implements CustomerProductService {
    @Autowired
    private CustomerProductDao customerProductDao;
    @Autowired
    private CustomerDao customerDao;

    @Override
    public void updateCustomerProductOutput() {
        List<CustomerProductDO> customerProductDOList = customerProductDao.selectCountByEnable();
        if(!CollectionUtils.isEmpty(customerProductDOList)){
            customerProductDOList.forEach(customerProductDO -> {
                customerProductDao.updateOutput(customerProductDO.getId(), customerProductDO.getPerformance());
                customerDao.updateSMCBalance(customerProductDO.getCustomerDO().getId(), customerProductDO.getPerformance());
            });

        }
    }
}
