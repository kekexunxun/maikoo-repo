package com.maikoo.superminercions.service.impl;

import com.github.pagehelper.PageHelper;
import com.maikoo.superminercions.config.CustomEnvironmentConfig;
import com.maikoo.superminercions.dao.*;
import com.maikoo.superminercions.exception.*;
import com.maikoo.superminercions.model.*;
import com.maikoo.superminercions.model.dto.SMCFeeDTO;
import com.maikoo.superminercions.model.dto.SMCTradingDTO;
import com.maikoo.superminercions.model.dto.SMCTradingInformationDTO;
import com.maikoo.superminercions.model.dto.SMCTradingWithCustomerDTO;
import com.maikoo.superminercions.model.query.OrderQuery;
import com.maikoo.superminercions.model.query.SMCTradingQuery;
import com.maikoo.superminercions.service.SMCTradingService;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.EncryptUtil;
import com.maikoo.superminercions.util.SerialNumberUtil;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.dao.DuplicateKeyException;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.util.CollectionUtils;

import javax.servlet.http.HttpSession;
import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

@Service
public class SMCTradingServiceImpl implements SMCTradingService {
    @Autowired
    private OrderDao orderDao;
    @Autowired
    private SMCOrderDao smcOrderDao;
    @Autowired
    private WithdrawalOrderDao withdrawalOrderDao;
    @Autowired
    private SMCLockDao smcLockDao;
    @Autowired
    private CustomerDao customerDao;
    @Autowired
    private SettingDao settingDao;
    @Autowired
    private HttpSession session;
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;

    @Override
    @Transactional
    public void buy(SMCTradingQuery smcTradingQuery) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        if (!customerDao.checkTradingPassword(currentCustomerDO.getId(), EncryptUtil.password(smcTradingQuery.getTransPass()))) {
            throw new InvalidTradingPasswordException();
        }

        SMCOrderDO smcOrderDO = new SMCOrderDO();
        smcOrderDO.setOrderSN(SerialNumberUtil.order());
        smcOrderDO.setCustomerDO(currentCustomerDO);
        smcOrderDO.setStatus(ConstantUtil.ORDER_STATUS_SMC_TRADING_PROCESSING);
        smcOrderDO.setQuantity(smcTradingQuery.getSmcNum());
        smcOrderDO.setBuyingPrice(smcTradingQuery.getBuyInPrice());
        smcOrderDO.setType(0);

        createdCommonOrder(smcOrderDO);

        SettingDO settingDO = settingDao.select();
        smcOrderDO.setPrice(settingDO.getSmcPrice());
        smcOrderDao.insert(smcOrderDO);
    }

    @Override
    @Transactional
    public void sell(SMCTradingQuery smcTradingQuery) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        if (!customerDao.checkTradingPassword(currentCustomerDO.getId(), EncryptUtil.password(smcTradingQuery.getTransPass()))) {
            throw new InvalidTradingPasswordException();
        }

        CustomerDO balance = customerDao.selectBalance(currentCustomerDO.getId());
        if (balance.getAvailableSMCBalance().compareTo(smcTradingQuery.getSmcNum()) < 0) {
            throw new InvalidFundsNotEnoughException("余额不足");
        }

        SMCOrderDO smcOrderDO = new SMCOrderDO();
        smcOrderDO.setOrderSN(SerialNumberUtil.order());
        smcOrderDO.setCustomerDO(currentCustomerDO);
        smcOrderDO.setStatus(ConstantUtil.ORDER_STATUS_SMC_TRADING_PROCESSING);
        smcOrderDO.setQuantity(smcTradingQuery.getSmcNum());
        smcOrderDO.setType(1);

        createdCommonOrder(smcOrderDO);

        SettingDO settingDO = settingDao.select();
        BigDecimal hundred = BigDecimal.valueOf(100);
        BigDecimal fee = BigDecimal.valueOf(settingDO.getSmcFee());
        smcOrderDO.setPrice(settingDO.getSmcPrice());
        smcOrderDO.setFee(smcOrderDO.getPrice().multiply(smcOrderDO.getQuantity()).multiply(fee).divide(hundred).setScale(6, BigDecimal.ROUND_HALF_EVEN));
        smcOrderDao.insert(smcOrderDO);

        // 更新用户表可用SMC资产
        CustomerDO newBalance = new CustomerDO();
        newBalance.setId(currentCustomerDO.getId());
        newBalance.setAvailableSMCBalance(balance.getAvailableSMCBalance().subtract(smcOrderDO.getQuantity()));
        customerDao.updateBalance(newBalance);
    }

    @Override
    public List<SMCTradingDTO> list(int pageNumber, int type) {
        List<SMCTradingDTO> smcTradingDTOList = new ArrayList<>();
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        try {
            PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
            List<Long> ids = smcOrderDao.selectPageIds(currentCustomerDO.getId(), type);
            if (ids != null && ids.size() > 0) {
                List<SMCOrderDO> smcOrderDOList = smcOrderDao.selectByIds(ids);
                smcOrderDOList.forEach(smcOrderDO -> smcTradingDTOList.add(SMCTradingDTO.valueOf(smcOrderDO)));
            }
        } catch (RuntimeException e) {
            throw new GetTradingRecordExcption("获取交易记录失败");
        }

        return smcTradingDTOList;
    }

    @Override
    public SMCTradingInformationDTO information(long orderSN) {
        SMCTradingInformationDTO smcTradingInformationDTO = null;
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        try {
            SMCOrderDO smcOrderDO = smcOrderDao.selectOne(orderSN);
            if (smcOrderDO != null) {
                if (smcOrderDO.getCustomerDO() == null || !smcOrderDO.getCustomerDO().getId().equals(currentCustomerDO.getId())) {
                    throw new InvalidParameterException("无效的订单编号");
                }
                smcTradingInformationDTO = smcTradingInformationDTO.valueOf(smcOrderDO);
            }
        } catch (Exception e) {
            throw new GetTradingRecordDetailException("交易记录详情获取失败");
        }

        return smcTradingInformationDTO;
    }

    @Override
    public SMCFeeDTO fee(String type) {
        SettingDO settingDO = settingDao.select();
        SMCFeeDTO smcFeeDTO = new SMCFeeDTO();
        if ("ALL".equals(type) || "SELL".equals(type)) {
            smcFeeDTO.setSmc2rmbRate(settingDO.getSmcFee());
        }

        if ("ALL".equals(type) || "EXTRACT".equals(type)) {
            smcFeeDTO.setSmcExtractRate(settingDO.getWithdrawalFee());
        }
        return smcFeeDTO;
    }

    @Override
    public List<SMCTradingWithCustomerDTO> listAll(int type) {
        List<SMCTradingWithCustomerDTO> smcTradingWithCustomerDTOList = new ArrayList<>();

        List<SMCOrderDO> smcOrderDOList = smcOrderDao.selectByType(type);
        if (!CollectionUtils.isEmpty(smcOrderDOList)) {
            smcOrderDOList.forEach(smcOrderDO -> smcTradingWithCustomerDTOList.add(SMCTradingWithCustomerDTO.valueOf(smcOrderDO)));
        }
        return smcTradingWithCustomerDTOList;
    }

    @Override
    @Transactional
    public void updateBuyStatus(OrderQuery orderQuery) {
        int status = orderQuery.isSuccess() ? ConstantUtil.ORDER_STATUS_COMPLETED : ConstantUtil.ORDER_STATUS_REJECTED;
        SMCOrderDO oldSMCOrderDO = smcOrderDao.selectOne(orderQuery.getListSn());

        updateOrderStatus(oldSMCOrderDO, status, orderQuery.getRemark());

        if (status == ConstantUtil.ORDER_STATUS_COMPLETED) {
            long customerId = oldSMCOrderDO.getCustomerDO().getId();
            CustomerDO balance = customerDao.selectBalance(customerId);
            CustomerDO customerDO = new CustomerDO();
            customerDO.setId(customerId);
            customerDO.setSmcBalance(balance.getSmcBalance().add(oldSMCOrderDO.getQuantity()));
            customerDO.setAvailableSMCBalance(balance.getAvailableSMCBalance().add(oldSMCOrderDO.getQuantity()));
            customerDao.updateBalance(customerDO);
        }
    }

    @Override
    @Transactional
    public void updateSellStatus(OrderQuery orderQuery) {
        int status = orderQuery.isSuccess() ? ConstantUtil.ORDER_STATUS_COMPLETED : ConstantUtil.ORDER_STATUS_REJECTED;
        SMCOrderDO oldSMCOrderDO = smcOrderDao.selectOne(orderQuery.getListSn());

        updateOrderStatus(oldSMCOrderDO, status, orderQuery.getRemark());

        long customerId = oldSMCOrderDO.getCustomerDO().getId();
        CustomerDO balance = customerDao.selectBalance(customerId);
        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(customerId);
        if (status == ConstantUtil.ORDER_STATUS_REJECTED) {
            customerDO.setAvailableSMCBalance(balance.getAvailableSMCBalance().add(oldSMCOrderDO.getQuantity()));
        }

        if (status == ConstantUtil.ORDER_STATUS_COMPLETED) {
            customerDO.setSmcBalance(balance.getSmcBalance().subtract(oldSMCOrderDO.getQuantity()));
        }
        customerDao.updateBalance(customerDO);
    }

    @Override
    public void updateWithdrawalStatus(OrderQuery orderQuery) {
        int status = orderQuery.isSuccess() ? ConstantUtil.ORDER_STATUS_COMPLETED : ConstantUtil.ORDER_STATUS_REJECTED;
        WithdrawalOrderDO oldWithdrawalOrderDO = withdrawalOrderDao.selectOne(orderQuery.getListSn());

        updateOrderStatus(oldWithdrawalOrderDO, status, orderQuery.getRemark());

        long customerId = oldWithdrawalOrderDO.getCustomerDO().getId();
        CustomerDO balance = customerDao.selectBalance(customerId);
        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(customerId);
        if (status == ConstantUtil.ORDER_STATUS_REJECTED) {
            customerDO.setAvailableSMCBalance(balance.getAvailableSMCBalance().add(oldWithdrawalOrderDO.getQuantity()));
        }

        if (status == ConstantUtil.ORDER_STATUS_COMPLETED) {
            customerDO.setSmcBalance(balance.getSmcBalance().subtract(oldWithdrawalOrderDO.getQuantity()));
        }
        customerDao.updateBalance(customerDO);
    }

    @Override
    public void updateLockStatus(OrderQuery orderQuery) {
        OrderDO orderDO = new OrderDO();
        orderDO.setOrderSN(orderQuery.getListSn());
        orderDO.setNote(orderQuery.getRemark());
        orderDao.updateStatus(orderDO);
    }

    @Override
    public void updateBuyNote(OrderQuery orderQuery) {
        OrderDO orderDO = new OrderDO();
        orderDO.setOrderSN(orderQuery.getListSn());
        orderDO.setNote(orderQuery.getRemark());
        orderDao.updateStatus(orderDO);
    }

    @Override
    public void updateSellNote(OrderQuery orderQuery) {
        OrderDO orderDO = new OrderDO();
        orderDO.setOrderSN(orderQuery.getListSn());
        orderDO.setNote(orderQuery.getRemark());
        orderDao.updateStatus(orderDO);
    }

    @Override
    public void updateWithdrawalNote(OrderQuery orderQuery) {
        OrderDO orderDO = new OrderDO();
        orderDO.setOrderSN(orderQuery.getListSn());
        orderDO.setNote(orderQuery.getRemark());
        orderDao.updateStatus(orderDO);
    }

    private void updateOrderStatus(OrderDO oldOrderDO, Integer status, String note) {
        if (oldOrderDO == null || oldOrderDO.getStatus() != ConstantUtil.ORDER_STATUS_PROCESSING) {
            throw new InvalidParameterException();
        }

        OrderDO orderDO = new OrderDO();
        orderDO.setOrderSN(oldOrderDO.getOrderSN());
        orderDO.setStatus(status);
        orderDO.setNote(note);
        orderDao.updateStatus(orderDO);
    }

    private void createdCommonOrder(SMCOrderDO smcOrderDO) {
        try {
            orderDao.insert(smcOrderDO);
        } catch (DuplicateKeyException e) {
            smcOrderDO.setOrderSN(SerialNumberUtil.order());
            createdCommonOrder(smcOrderDO);
        }
    }
}
