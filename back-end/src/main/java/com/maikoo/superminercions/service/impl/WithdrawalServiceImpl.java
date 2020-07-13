package com.maikoo.superminercions.service.impl;

import com.github.pagehelper.PageHelper;
import com.maikoo.superminercions.config.CustomEnvironmentConfig;
import com.maikoo.superminercions.dao.CustomerDao;
import com.maikoo.superminercions.dao.OrderDao;
import com.maikoo.superminercions.dao.SettingDao;
import com.maikoo.superminercions.dao.WithdrawalOrderDao;
import com.maikoo.superminercions.exception.InvalidFundsNotEnoughException;
import com.maikoo.superminercions.exception.InvalidParameterException;
import com.maikoo.superminercions.model.CustomerDO;
import com.maikoo.superminercions.model.SettingDO;
import com.maikoo.superminercions.model.WithdrawalOrderDO;
import com.maikoo.superminercions.model.dto.SMCWithdrawalWithCustomerDTO;
import com.maikoo.superminercions.model.dto.WithdrawalOrderDTO;
import com.maikoo.superminercions.model.dto.WithdrawalOrderInformationDTO;
import com.maikoo.superminercions.model.query.WithdrawalOrderQuery;
import com.maikoo.superminercions.service.WithdrawalService;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.SerialNumberUtil;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.dao.DuplicateKeyException;
import org.springframework.stereotype.Service;
import org.springframework.util.CollectionUtils;

import javax.servlet.http.HttpSession;
import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

@Service
public class WithdrawalServiceImpl implements WithdrawalService {
    @Autowired
    private OrderDao orderDao;
    @Autowired
    private WithdrawalOrderDao withdrawalOrderDao;
    @Autowired
    private CustomerDao customerDao;
    @Autowired
    private SettingDao settingDao;
    @Autowired
    private HttpSession session;
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;

    @Override
    public void withdrawal(WithdrawalOrderQuery withdrawalOrderQuery) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        CustomerDO balance = customerDao.selectBalance(currentCustomerDO.getId());
        if (balance.getAvailableSMCBalance().compareTo(withdrawalOrderQuery.getSmcNum()) < 0) {
            throw new InvalidFundsNotEnoughException("余额不足");
        }

        SettingDO settingDO = settingDao.select();
        BigDecimal hundred = BigDecimal.valueOf(100);
        BigDecimal fee = BigDecimal.valueOf(settingDO.getSmcFee());

        WithdrawalOrderDO withdrawalOrderDO = new WithdrawalOrderDO();
        withdrawalOrderDO.setOrderSN(SerialNumberUtil.order());
        withdrawalOrderDO.setStatus(ConstantUtil.ORDER_STATUS_PROCESSING);
        withdrawalOrderDO.setCustomerDO(currentCustomerDO);
        withdrawalOrderDO.setMethod(withdrawalOrderQuery.getExtractTo() - 1);
        withdrawalOrderDO.setQuantity(withdrawalOrderQuery.getSmcNum());
        withdrawalOrderDO.setPrice(settingDO.getSmcPrice());
        withdrawalOrderDO.setFee(withdrawalOrderDO.getQuantity().multiply(withdrawalOrderDO.getPrice()).multiply(fee).divide(hundred).setScale(6, BigDecimal.ROUND_HALF_EVEN));
        withdrawalOrderDO.setSmcBalance(balance.getSmcBalance());

        createdCommonOrder(withdrawalOrderDO);
        withdrawalOrderDao.insert(withdrawalOrderDO);

        // 更新用户表可用SMC资产
        CustomerDO newBalance = new CustomerDO();
        newBalance.setId(currentCustomerDO.getId());
        newBalance.setAvailableSMCBalance(balance.getAvailableSMCBalance().subtract(withdrawalOrderDO.getQuantity()));
        customerDao.updateBalance(newBalance);
    }

    @Override
    public List<WithdrawalOrderDTO> list(int pageNumber) {
        List<WithdrawalOrderDTO> withdrawalOrderDTOList = new ArrayList<>();
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = withdrawalOrderDao.selectPageIds(currentCustomerDO.getId());

        if (ids != null && ids.size() > 0) {
            List<WithdrawalOrderDO> withdrawalOrderDOList = withdrawalOrderDao.selectByIds(ids);
            withdrawalOrderDOList.forEach(withdrawalOrderDO -> withdrawalOrderDTOList.add(WithdrawalOrderDTO.valueOf(withdrawalOrderDO)));
        }

        return withdrawalOrderDTOList;
    }

    @Override
    public WithdrawalOrderInformationDTO information(long orderSN) {
        WithdrawalOrderInformationDTO withdrawalOrderInformationDTO = null;
        WithdrawalOrderDO withdrawalOrderDO = withdrawalOrderDao.selectOne(orderSN);
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        if (withdrawalOrderDO != null) {
            if (withdrawalOrderDO.getCustomerDO() == null || !withdrawalOrderDO.getCustomerDO().getId().equals(currentCustomerDO.getId())) {
                throw new InvalidParameterException("无效的订单编号");
            }

            withdrawalOrderInformationDTO = WithdrawalOrderInformationDTO.valueOf(withdrawalOrderDO);
        } else {
            withdrawalOrderInformationDTO = new WithdrawalOrderInformationDTO();
        }

        return withdrawalOrderInformationDTO;
    }

    @Override
    public List<SMCWithdrawalWithCustomerDTO> listAll() {
        List<SMCWithdrawalWithCustomerDTO> smcWithdrawalWithCustomerDTOList = new ArrayList<>();
        List<WithdrawalOrderDO> withdrawalOrderDOList = withdrawalOrderDao.selectAllWithCustomer();

        if (!CollectionUtils.isEmpty(withdrawalOrderDOList)) {
            withdrawalOrderDOList.forEach(withdrawalOrderDO -> smcWithdrawalWithCustomerDTOList.add(SMCWithdrawalWithCustomerDTO.valueOf(withdrawalOrderDO)));
        }

        return smcWithdrawalWithCustomerDTOList;
    }

    private void createdCommonOrder(WithdrawalOrderDO withdrawalOrderDO) {
        try{
            orderDao.insert(withdrawalOrderDO);
        }catch (DuplicateKeyException e){
            withdrawalOrderDO.setOrderSN(SerialNumberUtil.order());
            createdCommonOrder(withdrawalOrderDO);
        }
    }
}
