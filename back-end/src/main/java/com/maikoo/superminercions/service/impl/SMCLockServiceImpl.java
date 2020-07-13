package com.maikoo.superminercions.service.impl;

import com.github.pagehelper.PageHelper;
import com.maikoo.superminercions.config.CustomEnvironmentConfig;
import com.maikoo.superminercions.dao.CustomerDao;
import com.maikoo.superminercions.dao.OrderDao;
import com.maikoo.superminercions.dao.SMCLockDao;
import com.maikoo.superminercions.dao.SettingDao;
import com.maikoo.superminercions.exception.InvalidFundsNotEnoughException;
import com.maikoo.superminercions.exception.InvalidParameterException;
import com.maikoo.superminercions.model.CustomerDO;
import com.maikoo.superminercions.model.SMCLockCycleDO;
import com.maikoo.superminercions.model.SMCLockDO;
import com.maikoo.superminercions.model.dto.SMCLockCycleDTO;
import com.maikoo.superminercions.model.dto.SMCLockDTO;
import com.maikoo.superminercions.model.dto.SMCLockInformationDTO;
import com.maikoo.superminercions.model.dto.SMCLockWithCustomerDTO;
import com.maikoo.superminercions.service.SMCLockService;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.SerialNumberUtil;
import org.apache.ibatis.session.ExecutorType;
import org.apache.ibatis.session.SqlSession;
import org.apache.ibatis.session.SqlSessionFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.dao.DuplicateKeyException;
import org.springframework.stereotype.Service;
import org.springframework.util.CollectionUtils;

import javax.servlet.http.HttpSession;
import java.math.BigDecimal;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.ZoneOffset;
import java.util.ArrayList;
import java.util.List;

@Service
public class SMCLockServiceImpl implements SMCLockService {
    @Autowired
    private SMCLockDao smcLockDao;
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
    @Autowired
    private SqlSessionFactory sqlSessionFactory;

    @Override
    public List<SMCLockCycleDTO> lockCycle() {
        List<SMCLockCycleDTO> smcLockCycleDTOList = new ArrayList<>();

        List<SMCLockCycleDO> smcLockCycleDOList = smcLockDao.selectAllLockCycle();
        if (smcLockCycleDOList != null && smcLockCycleDOList.size() > 0) {
            smcLockCycleDOList.forEach(smcLockCycleDO -> smcLockCycleDTOList.add(SMCLockCycleDTO.valueOf(smcLockCycleDO)));
        }

        return smcLockCycleDTOList;
    }

    @Override
    public void apply(long lockCycleId, BigDecimal smcQuantity) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        CustomerDO balance = customerDao.selectBalance(currentCustomerDO.getId());
        if (balance.getAvailableSMCBalance().compareTo(smcQuantity) < 0) {
            throw new InvalidFundsNotEnoughException("余额不足");
        }

        SMCLockDO smcLockDO = new SMCLockDO();
        smcLockDO.setOrderSN(SerialNumberUtil.order());
        smcLockDO.setCustomerDO(currentCustomerDO);
        smcLockDO.setStatus(ConstantUtil.ORDER_STATUS_PROCESSING);
        smcLockDO.setQuantity(smcQuantity);
        SMCLockCycleDO smcLockCycleDO = new SMCLockCycleDO();
        smcLockCycleDO.setId(lockCycleId);
        smcLockDO.setSmcLockCycleDO(smcLockCycleDO);

        createdCommonOrder(smcLockDO);
        smcLockDao.insert(smcLockDO);

        // 更新用户表可用SMC资产
        CustomerDO newBalance = new CustomerDO();
        newBalance.setId(currentCustomerDO.getId());
        newBalance.setAvailableSMCBalance(balance.getAvailableSMCBalance().subtract(smcQuantity));
        customerDao.updateBalance(newBalance);
    }

    @Override
    public List<SMCLockDTO> list(int pageNumber) {
        List<SMCLockDTO> smcLockDTOList = new ArrayList<>();
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = smcLockDao.selectPageIds(currentCustomerDO.getId());

        if (ids != null && ids.size() > 0) {
            List<SMCLockDO> smcLockDOList = smcLockDao.selectByIds(ids);
            smcLockDOList.forEach(smcLockDO -> smcLockDTOList.add(SMCLockDTO.valueOf(smcLockDO)));
        }
        return smcLockDTOList;
    }

    @Override
    public SMCLockInformationDTO information(long orderSN) {
        SMCLockInformationDTO smcLockInformationDTO = null;
        SMCLockDO smcLockDO = smcLockDao.selectOne(orderSN);
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        if (smcLockDO != null && smcLockDO.getCustomerDO() != null && smcLockDO.getCustomerDO().getId().equals(currentCustomerDO.getId())) {
            smcLockInformationDTO = SMCLockInformationDTO.valueOf(smcLockDO);
        } else {
            throw new InvalidParameterException("无效的订单编号");
        }
        return smcLockInformationDTO;
    }

    @Override
    public List<SMCLockWithCustomerDTO> listAll() {
        List<SMCLockWithCustomerDTO> smcLockWithCustomerDTOList = new ArrayList<>();
        List<SMCLockDO> smcLockDOList = smcLockDao.selectAllWithCustomer();
        if (!CollectionUtils.isEmpty(smcLockDOList)) {
            smcLockDOList.forEach(smcLockDO -> smcLockWithCustomerDTOList.add(SMCLockWithCustomerDTO.valueOf(smcLockDO)));
        }
        return smcLockWithCustomerDTOList;
    }

    @Override
    public void updateLockReward() {
        SqlSession sqlSession = sqlSessionFactory.openSession(ExecutorType.BATCH);

        try {
            LocalDateTime todayDateTime = LocalDate.now().atStartOfDay();
            long todayTimeStamp = todayDateTime.toEpochSecond(ZoneOffset.of("+8"));
            List<SMCLockDO> smcLockDOList = smcLockDao.selectExceedLockCycleByOrderStatus(todayTimeStamp);
            smcLockDOList.forEach(smcLockDO -> {
                smcLockDO.setStatus(ConstantUtil.ORDER_STATUS_COMPLETED);
                orderDao.updateStatus(smcLockDO);
                BigDecimal hundred = BigDecimal.valueOf(100);
                BigDecimal reward = BigDecimal.valueOf(smcLockDO.getSmcLockCycleDO().getReward());
                BigDecimal smcReward = smcLockDO.getQuantity().multiply(reward).divide(hundred).setScale(6, BigDecimal.ROUND_HALF_EVEN);
                smcLockDO.getCustomerDO().setSmcBalance(smcReward.add(smcLockDO.getCustomerDO().getSmcBalance()));
                smcLockDO.getCustomerDO().setAvailableSMCBalance(smcReward.add(smcLockDO.getCustomerDO().getAvailableSMCBalance()).add(smcLockDO.getQuantity()));
                customerDao.updateBalance(smcLockDO.getCustomerDO());
            });

            sqlSession.commit();
        } finally {
            sqlSession.close();
        }

    }

    private void createdCommonOrder(SMCLockDO smcLockDO) {
        try{
            orderDao.insert(smcLockDO);
        }catch (DuplicateKeyException e){
            smcLockDO.setOrderSN(SerialNumberUtil.order());
            createdCommonOrder(smcLockDO);
        }
    }
}
