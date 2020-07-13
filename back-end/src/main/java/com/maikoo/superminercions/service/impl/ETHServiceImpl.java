package com.maikoo.superminercions.service.impl;

import com.github.pagehelper.PageHelper;
import com.maikoo.superminercions.config.CustomEnvironmentConfig;
import com.maikoo.superminercions.dao.*;
import com.maikoo.superminercions.exception.GetTradingRecordExcption;
import com.maikoo.superminercions.exception.InvalidFundsNotEnoughException;
import com.maikoo.superminercions.exception.InvalidParameterException;
import com.maikoo.superminercions.exception.InvalidTradingPasswordException;
import com.maikoo.superminercions.model.*;
import com.maikoo.superminercions.model.dto.*;
import com.maikoo.superminercions.model.query.ETHSellQuery;
import com.maikoo.superminercions.model.query.ETHSwapSMCQuery;
import com.maikoo.superminercions.model.query.ETHWithdrawalQuery;
import com.maikoo.superminercions.model.query.OrderQuery;
import com.maikoo.superminercions.service.ETHService;
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
import java.util.List;

@Service
public class ETHServiceImpl implements ETHService {
    @Autowired
    private ETHWithdrawalDao ethWithdrawalDao;
    @Autowired
    private ETHSwapSMCDao ethSwapSMCDao;
    @Autowired
    private ETHSellDao ethSellDao;
    @Autowired
    private OrderDao orderDao;
    @Autowired
    private CustomerDao customerDao;
    @Autowired
    private SettingDao settingDao;
    @Autowired
    private HttpSession session;
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;

    @Override
    public void applyWithdrawal(ETHWithdrawalQuery ethWithdrawalQuery) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        if (!customerDao.checkTradingPassword(currentCustomerDO.getId(), EncryptUtil.password(ethWithdrawalQuery.getTransPass()))) {
            throw new InvalidTradingPasswordException();
        }

        CustomerDO balance = customerDao.selectBalance(currentCustomerDO.getId());
        if (balance.getAvailableETHBalance().compareTo(ethWithdrawalQuery.getEthNum()) < 0) {
            throw new InvalidFundsNotEnoughException("余额不足");
        }

        ETHWithdrawalDO ethWithdrawalDO = new ETHWithdrawalDO();
        ethWithdrawalDO.setOrderSN(SerialNumberUtil.order());
        ethWithdrawalDO.setCustomerDO(currentCustomerDO);
        ethWithdrawalDO.setStatus(ConstantUtil.ORDER_STATUS_PROCESSING);
        ethWithdrawalDO.setQuantity(ethWithdrawalQuery.getEthNum());
        ethWithdrawalDO.setWalletAddress(ethWithdrawalQuery.getWalletAddress());

        createdCommonOrder(ethWithdrawalDO);
        ethWithdrawalDao.insert(ethWithdrawalDO);

        // 更新用户表可用ETH资产
        CustomerDO newBalance = new CustomerDO();
        newBalance.setId(currentCustomerDO.getId());
        newBalance.setAvailableETHBalance(balance.getAvailableETHBalance().subtract(ethWithdrawalDO.getQuantity()));
        customerDao.updateBalance(newBalance);
    }

    @Override
    public List<ETHWithdrawalDTO> withdrawalList(int pageNumber) {
        List<ETHWithdrawalDTO> ethWithdrawalDTOList = new ArrayList<>();
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        try {
            PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
            List<Long> ids = ethWithdrawalDao.selectPageIds(currentCustomerDO.getId());

            if (ids != null && ids.size() > 0) {
                List<ETHWithdrawalDO> ethWithdrawalDOList = ethWithdrawalDao.selectByIds(ids);
                ethWithdrawalDOList.forEach(ethWithdrawalDO -> ethWithdrawalDTOList.add(ETHWithdrawalDTO.valueOf(ethWithdrawalDO)));
            }
        } catch (Exception e) {
            throw new GetTradingRecordExcption("交易记录获取失败");
        }
        return ethWithdrawalDTOList;
    }

    @Override
    public ETHWithdrawalInformationDTO withdrawalInformation(long orderSN) {
        ETHWithdrawalInformationDTO ethWithdrawalInformationDTO = null;
        ETHWithdrawalDO ethWithdrawalDO = ethWithdrawalDao.selectOne(orderSN);
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        if (ethWithdrawalDO != null && ethWithdrawalDO.getCustomerDO() != null && ethWithdrawalDO.getCustomerDO().getId().equals(currentCustomerDO.getId())) {
            ethWithdrawalInformationDTO = ETHWithdrawalInformationDTO.valueOf(ethWithdrawalDO);
        } else {
            throw new InvalidParameterException("无效的订单编号");
        }
        return ethWithdrawalInformationDTO;
    }

    @Override
    public void exchangeSMC(ETHSwapSMCQuery ethSwapSMCQuery) {
        ethSwapSMC(ethSwapSMCQuery, ConstantUtil.ETH_EXCHANGE_SMC);
    }

    @Override
    public List<ETHExchangeSMCDTO> exchangeSMCList(int pageNumber) {
        List<ETHExchangeSMCDTO> ethExchangeSMCDTOList = new ArrayList<>();
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        try {
            PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
            List<Long> ids = ethSwapSMCDao.selectPageIdsByType(currentCustomerDO.getId(), ConstantUtil.ETH_EXCHANGE_SMC);
            if (ids != null && ids.size() > 0) {
                List<ETHSwapSMCDO> ethSwapSMCDOList = ethSwapSMCDao.selectByIds(ids);
                ethSwapSMCDOList.forEach(ethSwapSMCDO -> ethExchangeSMCDTOList.add(ETHExchangeSMCDTO.valueOf(ethSwapSMCDO)));
            }
        } catch (Exception e) {
            throw new GetTradingRecordExcption("交易记录获取失败");
        }
        return ethExchangeSMCDTOList;
    }

    @Override
    public ETHExchangeSMCInformationDTO exchangeSMCInformation(long orderSN) {
        ETHExchangeSMCInformationDTO ethExchangeSMCInformationDTO = null;
        ETHSwapSMCDO ethSwapSMCDO = ethSwapSMCDao.selectOne(orderSN);
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        if (ethSwapSMCDO != null && ethSwapSMCDO.getCustomerDO() != null && ethSwapSMCDO.getCustomerDO().getId().equals(currentCustomerDO.getId())) {
            ethExchangeSMCInformationDTO = ETHExchangeSMCInformationDTO.valueOf(ethSwapSMCDO);
        } else {
            throw new InvalidParameterException("无效的订单编号");
        }
        return ethExchangeSMCInformationDTO;
    }

    @Override
    public void buy(ETHSwapSMCQuery ethSwapSMCQuery) {
        ethSwapSMC(ethSwapSMCQuery, ConstantUtil.SMC_EXCHANGE_ETH);
    }

    @Override
    public List<ETHBuyDTO> buyList(int pageNumber) {
        List<ETHBuyDTO> ethBuyDTOList = new ArrayList<>();
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        try {
            PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
            List<Long> ids = ethSwapSMCDao.selectPageIdsByType(currentCustomerDO.getId(), ConstantUtil.SMC_EXCHANGE_ETH);
            if (ids != null && ids.size() > 0) {
                List<ETHSwapSMCDO> ethSwapSMCDOList = ethSwapSMCDao.selectByIds(ids);
                ethSwapSMCDOList.forEach(ethSwapSMCDO -> ethBuyDTOList.add(ETHBuyDTO.valueOf(ethSwapSMCDO)));
            }
        } catch (Exception e) {
            throw new GetTradingRecordExcption("交易记录获取失败");
        }
        return ethBuyDTOList;
    }

    @Override
    public ETHBuyInformationDTO buyInformation(long orderSN) {
        ETHBuyInformationDTO ethBuyInformationDTO = null;
        try {
            ETHSwapSMCDO ethSwapSMCDO = ethSwapSMCDao.selectOne(orderSN);
            CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

            if (ethSwapSMCDO != null && ethSwapSMCDO.getCustomerDO() != null && ethSwapSMCDO.getCustomerDO().getId().equals(currentCustomerDO.getId())) {
                ethBuyInformationDTO = ETHBuyInformationDTO.valueOf(ethSwapSMCDO);
            }
        } catch (Exception e) {
            throw new InvalidParameterException("无效的订单编号");
        }
        return ethBuyInformationDTO;
    }

    @Override
    public void sell(ETHSellQuery ethSellQuery) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        if (!customerDao.checkTradingPassword(currentCustomerDO.getId(), EncryptUtil.password(ethSellQuery.getTransPass()))) {
            throw new InvalidTradingPasswordException();
        }
        CustomerDO balance = balance = customerDao.selectBalance(currentCustomerDO.getId());
        if (balance.getAvailableETHBalance().compareTo(ethSellQuery.getEthNum()) < 0) {
            throw new InvalidFundsNotEnoughException("余额不足");
        }

        SettingDO settingDO = settingDao.select();
        ETHSellDO ethSellDO = new ETHSellDO();
        ethSellDO.setOrderSN(SerialNumberUtil.order());
        ethSellDO.setCustomerDO(currentCustomerDO);
        ethSellDO.setStatus(ConstantUtil.ORDER_STATUS_PROCESSING);
        ethSellDO.setQuantity(ethSellQuery.getEthNum());
        ethSellDO.setPrice(ethSellQuery.getEthPrice());
        ethSellDO.setCurrentPrice(settingDO.getEthPrice());

        createdCommonOrder(ethSellDO);
        ethSellDao.insert(ethSellDO);

        // 更新用户表可用ETH资产
        CustomerDO newBalance = new CustomerDO();
        newBalance.setId(currentCustomerDO.getId());
        newBalance.setAvailableETHBalance(balance.getAvailableETHBalance().subtract(ethSellDO.getQuantity()));
        customerDao.updateBalance(newBalance);
    }

    @Override
    public List<ETHSellDTO> sellList(int pageNumber) {
        List<ETHSellDTO> ethSellDTOList = new ArrayList<>();
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        try {
            PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
            List<Long> ids = ethSellDao.selectPageIds(currentCustomerDO.getId());
            if (ids != null && ids.size() > 0) {
                List<ETHSellDO> ethSellDOList = ethSellDao.selectByIds(ids);
                ethSellDOList.forEach(ethSellDO -> ethSellDTOList.add(ETHSellDTO.valueOf(ethSellDO)));
            }
        } catch (Exception e) {
            throw new GetTradingRecordExcption("交易记录获取失败");
        }

        return ethSellDTOList;
    }

    @Override
    public ETHSellInformationDTO sellInformation(long orderSN) {
        ETHSellInformationDTO ethSellInformationDTO = null;
        try {
            ETHSellDO ethSellDO = ethSellDao.selectOne(orderSN);
            CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
            if (ethSellDO != null && ethSellDO.getCustomerDO() != null && ethSellDO.getCustomerDO().getId().equals(currentCustomerDO.getId())) {
                ethSellInformationDTO = ETHSellInformationDTO.valueOf(ethSellDO);
            }
        } catch (Exception e) {
            throw new InvalidParameterException("无效的订单编号");
        }
        return ethSellInformationDTO;
    }

    @Override
    public List<ETHBuyWithCustomerDTO> buyListAll() {
        List<ETHBuyWithCustomerDTO> ethBuyWithCustomerDTOList = new ArrayList<>();
        List<ETHSwapSMCDO> ethSwapSMCDOList = ethSwapSMCDao.selectAllWithCustomerByType(0);
        if (!CollectionUtils.isEmpty(ethSwapSMCDOList)) {
            ethSwapSMCDOList.forEach(ethSwapSMCDO -> ethBuyWithCustomerDTOList.add(ETHBuyWithCustomerDTO.valueOf(ethSwapSMCDO)));
        }
        return ethBuyWithCustomerDTOList;
    }

    @Override
    @Transactional
    public void updateBuyStatus(OrderQuery orderQuery) {
        int status = orderQuery.isSuccess() ? ConstantUtil.ORDER_STATUS_COMPLETED : ConstantUtil.ORDER_STATUS_REJECTED;
        ETHSwapSMCDO ethSwapSMCDO = ethSwapSMCDao.selectOne(orderQuery.getListSn());

        updateOrderStatus(ethSwapSMCDO, status, orderQuery.getRemark());

        long customerId = ethSwapSMCDO.getCustomerDO().getId();
        BigDecimal ethQuantity = ethSwapSMCDO.getEthQuantity();
        BigDecimal smcQuantity = ethSwapSMCDO.getSmcQuantity();
        CustomerDO balance = customerDao.selectBalance(customerId);
        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(customerId);
        if (status == ConstantUtil.ORDER_STATUS_REJECTED) {
            customerDO.setAvailableSMCBalance(balance.getAvailableSMCBalance().add(smcQuantity));
        }

        if (status == ConstantUtil.ORDER_STATUS_COMPLETED) {
            customerDO.setSmcBalance(balance.getSmcBalance().subtract(smcQuantity));
            customerDO.setEthBalance(balance.getEthBalance().add(ethQuantity));
            customerDO.setAvailableETHBalance(balance.getAvailableETHBalance().add(ethQuantity));
        }
        customerDao.updateBalance(customerDO);
    }

    @Override
    public List<ETHSellWithCustomerDTO> sellListAll() {
        List<ETHSellWithCustomerDTO> ethSellWithCustomerDTOList = new ArrayList<>();
        List<ETHSellDO> ethSellDOList = ethSellDao.selectAllWithCustomer();

        if (!CollectionUtils.isEmpty(ethSellDOList)) {
            ethSellDOList.forEach(ethSellDO -> ethSellWithCustomerDTOList.add(ETHSellWithCustomerDTO.valueOf(ethSellDO)));
        }

        return ethSellWithCustomerDTOList;
    }

    @Override
    @Transactional
    public void updateSellStatus(OrderQuery orderQuery) {
        int status = orderQuery.isSuccess() ? ConstantUtil.ORDER_STATUS_COMPLETED : ConstantUtil.ORDER_STATUS_REJECTED;
        ETHSellDO oldETHSellDO = ethSellDao.selectOne(orderQuery.getListSn());

        updateOrderStatus(oldETHSellDO, status, orderQuery.getRemark());

        long customerId = oldETHSellDO.getCustomerDO().getId();
        BigDecimal ethQuantity = oldETHSellDO.getQuantity();
        CustomerDO balance = customerDao.selectBalance(customerId);
        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(customerId);
        if (status == ConstantUtil.ORDER_STATUS_REJECTED) {
            customerDO.setAvailableETHBalance(balance.getAvailableETHBalance().add(ethQuantity));
        }
        if (status == ConstantUtil.ORDER_STATUS_COMPLETED) {
            customerDO.setEthBalance(balance.getEthBalance().subtract(ethQuantity));
        }
        customerDao.updateBalance(customerDO);
    }

    @Override
    public List<ETHWithdrawalWithCustomerDTO> withdrawalListAll() {
        List<ETHWithdrawalWithCustomerDTO> ethWithdrawalWithCustomerDTOList = new ArrayList<>();
        List<ETHWithdrawalDO> ethWithdrawalDOList = ethWithdrawalDao.selectAllWithCustomer();
        if (!CollectionUtils.isEmpty(ethWithdrawalDOList)) {
            ethWithdrawalDOList.forEach(ethWithdrawalDO -> ethWithdrawalWithCustomerDTOList.add(ETHWithdrawalWithCustomerDTO.valueOf(ethWithdrawalDO)));
        }
        return ethWithdrawalWithCustomerDTOList;
    }

    @Override
    @Transactional
    public void updateWithdrawalStatus(OrderQuery orderQuery) {
        int status = orderQuery.isSuccess() ? ConstantUtil.ORDER_STATUS_COMPLETED : ConstantUtil.ORDER_STATUS_REJECTED;
        ETHWithdrawalDO oldETHWithdrawalDO = ethWithdrawalDao.selectOne(orderQuery.getListSn());

        updateOrderStatus(oldETHWithdrawalDO, status, orderQuery.getRemark());

        long customerId = oldETHWithdrawalDO.getCustomerDO().getId();
        BigDecimal ethQuantity = oldETHWithdrawalDO.getQuantity();
        CustomerDO balance = customerDao.selectBalance(customerId);
        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(customerId);
        if (status == ConstantUtil.ORDER_STATUS_REJECTED) {
            customerDO.setAvailableETHBalance(balance.getAvailableETHBalance().add(ethQuantity));
        }
        if (status == ConstantUtil.ORDER_STATUS_COMPLETED) {
            customerDO.setEthBalance(balance.getEthBalance().subtract(ethQuantity));
        }
        customerDao.updateBalance(customerDO);
    }

    @Override
    public List<ETHExchangeSMCWithCustomerDTO> exchangeSMCListAll() {
        List<ETHExchangeSMCWithCustomerDTO> ethExchangeSMCWithCustomerDTOList = new ArrayList<>();
        List<ETHSwapSMCDO> ethSwapSMCDOList = ethSwapSMCDao.selectAllWithCustomerByType(1);
        if (!CollectionUtils.isEmpty(ethSwapSMCDOList)) {
            ethSwapSMCDOList.forEach(ethSwapSMCDO -> ethExchangeSMCWithCustomerDTOList.add(ETHExchangeSMCWithCustomerDTO.valueOf(ethSwapSMCDO)));
        }
        return ethExchangeSMCWithCustomerDTOList;

    }

    @Override
    @Transactional
    public void updateExchangeSMCStatus(OrderQuery orderQuery) {
        int status = orderQuery.isSuccess() ? ConstantUtil.ORDER_STATUS_COMPLETED : ConstantUtil.ORDER_STATUS_REJECTED;
        ETHSwapSMCDO ethSwapSMCDO = ethSwapSMCDao.selectOne(orderQuery.getListSn());

        updateOrderStatus(ethSwapSMCDO, status, orderQuery.getRemark());

        long customerId = ethSwapSMCDO.getCustomerDO().getId();
        BigDecimal ethQuantity = ethSwapSMCDO.getEthQuantity();
        BigDecimal smcQuantity = ethSwapSMCDO.getSmcQuantity();
        CustomerDO balance = customerDao.selectBalance(customerId);
        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(customerId);
        if (status == ConstantUtil.ORDER_STATUS_REJECTED) {
            customerDO.setAvailableETHBalance(balance.getAvailableETHBalance().add(ethQuantity));
        }

        if (status == ConstantUtil.ORDER_STATUS_COMPLETED) {
            customerDO.setEthBalance(balance.getEthBalance().subtract(ethQuantity));
            customerDO.setSmcBalance(balance.getSmcBalance().add(smcQuantity));
            customerDO.setAvailableSMCBalance(balance.getAvailableSMCBalance().add(smcQuantity));
        }
        customerDao.updateBalance(customerDO);
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

    @Override
    public void updateExchangeSMCNote(OrderQuery orderQuery) {
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

    private void createdCommonOrder(OrderDO orderDO) {
        try{
            orderDao.insert(orderDO);
        }catch (DuplicateKeyException e){
            orderDO.setOrderSN(SerialNumberUtil.order());
            createdCommonOrder(orderDO);
        }
    }

    private void ethSwapSMC(ETHSwapSMCQuery ethSwapSMCQuery, int type) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        
        if (!customerDao.checkTradingPassword(currentCustomerDO.getId(), EncryptUtil.password(ethSwapSMCQuery.getTransPass()))) {
            throw new InvalidTradingPasswordException();
        }

        CustomerDO balance = customerDao.selectBalance(currentCustomerDO.getId());
        SettingDO settingDO = settingDao.select();
        if ((type == ConstantUtil.ETH_EXCHANGE_SMC
                && balance.getAvailableETHBalance().compareTo(ethSwapSMCQuery.getEthNum()) < 0)
                || (type == ConstantUtil.SMC_EXCHANGE_ETH
                && balance.getAvailableSMCBalance().compareTo(ethSwapSMCQuery.getSmcNum()) < 0)) {
            throw new InvalidFundsNotEnoughException("余额不足");
        }

        ETHSwapSMCDO ethSwapSMCDO = new ETHSwapSMCDO();
        ethSwapSMCDO.setOrderSN(SerialNumberUtil.order());
        ethSwapSMCDO.setCustomerDO(currentCustomerDO);
        ethSwapSMCDO.setStatus(ConstantUtil.ORDER_STATUS_PROCESSING);
        ethSwapSMCDO.setType(type);
        ethSwapSMCDO.setEthPrice(settingDO.getEthPrice());
        ethSwapSMCDO.setSmcPrice(settingDO.getSmcPrice());
        ethSwapSMCDO.setEthQuantity(type == ConstantUtil.ETH_EXCHANGE_SMC ?
                ethSwapSMCQuery.getEthNum() :
                ethSwapSMCQuery.getSmcNum()
                        .multiply(settingDO.getSmcPrice())
                        .divide(settingDO.getEthPrice(), 6, BigDecimal.ROUND_HALF_EVEN));
        ethSwapSMCDO.setSmcQuantity(type == ConstantUtil.SMC_EXCHANGE_ETH ?
                ethSwapSMCQuery.getSmcNum() :
                ethSwapSMCQuery.getEthNum()
                        .multiply(settingDO.getEthPrice())
                        .divide(settingDO.getSmcPrice(), 6, BigDecimal.ROUND_HALF_EVEN));

        createdCommonOrder(ethSwapSMCDO);
        ethSwapSMCDao.insert(ethSwapSMCDO);

        if (type == ConstantUtil.ETH_EXCHANGE_SMC) {
            // 更新用户表可用ETH资产
            CustomerDO newBalance = new CustomerDO();
            newBalance.setId(currentCustomerDO.getId());
            newBalance.setAvailableETHBalance(balance.getAvailableETHBalance().subtract(ethSwapSMCDO.getEthQuantity()));
            customerDao.updateBalance(newBalance);
        } else if (type == ConstantUtil.SMC_EXCHANGE_ETH) {
            // 更新用户表可用SMC资产
            CustomerDO newBalance = new CustomerDO();
            newBalance.setId(currentCustomerDO.getId());
            newBalance.setAvailableSMCBalance(balance.getAvailableSMCBalance().subtract(ethSwapSMCDO.getSmcQuantity()));
            customerDao.updateBalance(newBalance);
        }
    }
}
