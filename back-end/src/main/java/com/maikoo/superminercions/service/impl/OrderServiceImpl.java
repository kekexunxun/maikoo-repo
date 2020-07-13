package com.maikoo.superminercions.service.impl;

import com.maikoo.superminercions.dao.OrderDao;
import com.maikoo.superminercions.model.CustomerDO;
import com.maikoo.superminercions.service.OrderService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class OrderServiceImpl implements OrderService {
    @Autowired
    private OrderDao orderDao;
    @Override
    public Long countOrderByUser(Long userId) {
        Long aLong= orderDao.countOrderByUser(userId);
        return aLong;
    }
}
