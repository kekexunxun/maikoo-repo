package com.maikoo.superminercions.service.impl;

import com.maikoo.superminercions.dao.SMCLockDao;
import com.maikoo.superminercions.dao.SMCPriceDao;
import com.maikoo.superminercions.dao.SettingDao;
import com.maikoo.superminercions.model.SMCLockCycleDO;
import com.maikoo.superminercions.model.SMCPriceDO;
import com.maikoo.superminercions.model.SettingDO;
import com.maikoo.superminercions.model.dto.SMCLockCycleDTO;
import com.maikoo.superminercions.model.dto.SettingDTO;
import com.maikoo.superminercions.model.query.SettingQuery;
import com.maikoo.superminercions.service.SettingService;
import org.apache.ibatis.session.ExecutorType;
import org.apache.ibatis.session.SqlSession;
import org.apache.ibatis.session.SqlSessionFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.util.CollectionUtils;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.ZoneOffset;
import java.util.ArrayList;
import java.util.List;

@Service
public class SettingServiceImpl implements SettingService {
    @Autowired
    private SettingDao settingDao;
    @Autowired
    private SMCLockDao smcLockDao;
    @Autowired
    private SMCPriceDao smcPriceDao;
    @Autowired
    private SqlSessionFactory sqlSessionFactory;

    @Override
    public SettingDTO information() {
        SettingDTO settingDTO = new SettingDTO();
        SettingDO settingDO = settingDao.select();
        List<SMCLockCycleDO> smcLockCycleDOList = smcLockDao.selectAllLockCycle();

        if (settingDO == null || CollectionUtils.isEmpty(smcLockCycleDOList)) {
            throw new RuntimeException("无法获取设置信息、锁仓周期列表");
        }
        List<SMCLockCycleDTO> smcLockCycleDTOList = new ArrayList<>();
        smcLockCycleDOList.forEach(smcLockCycleDO -> smcLockCycleDTOList.add(SMCLockCycleDTO.valueOf(smcLockCycleDO)));
        settingDTO.setSmc2rmb(settingDO.getSmcPrice());
        settingDTO.setEth2rmb(settingDO.getEthPrice());
        settingDTO.setSmc2rmbRate(settingDO.getSmcFee());
        settingDTO.setSmcExtractRate(settingDO.getWithdrawalFee());
        settingDTO.setLpList(smcLockCycleDTOList);
        return settingDTO;
    }

    @Override
    public void update(SettingQuery settingQuery) {
        SqlSession sqlSession = sqlSessionFactory.openSession(ExecutorType.BATCH);

        try {
            SettingDO settingDO = new SettingDO();
            settingDO.setSmcPrice(settingQuery.getSmc2rmb());
            settingDO.setEthPrice(settingQuery.getEth2rmb());
            settingDO.setSmcFee(settingQuery.getSmc2rmbRate());
            settingDO.setWithdrawalFee(settingQuery.getSmcExtractRate());
            settingDao.update(settingDO);

            List<SMCLockCycleDO> smcLockCycleDOList = new ArrayList<>();
            settingQuery.getLpList().forEach(smcLockCycleQuery -> {
                SMCLockCycleDO smcLockCycleDO = new SMCLockCycleDO();
                smcLockCycleDO.setId(smcLockCycleQuery.getLpId());
                smcLockCycleDO.setCycle(smcLockCycleQuery.getLpDate());
                smcLockCycleDO.setReward(smcLockCycleQuery.getLpRate());
                smcLockCycleDOList.add(smcLockCycleDO);
            });
            smcLockCycleDOList.forEach(smcLockCycleDO -> smcLockDao.updateLockCycle(smcLockCycleDO));

            LocalDateTime todayDateTime = LocalDate.now().atStartOfDay();
            long todayTimeStamp = todayDateTime.toEpochSecond(ZoneOffset.of("+8"));
            SMCPriceDO smcPriceDO = smcPriceDao.selectOneByDate(todayTimeStamp);
            if(smcPriceDO == null){
                smcPriceDO = new SMCPriceDO();
                smcPriceDO.setDate(todayTimeStamp);
                smcPriceDO.setPrice(settingDO.getSmcPrice());
                smcPriceDao.insert(smcPriceDO);
            }else{
                smcPriceDO.setPrice(settingDO.getSmcPrice());
                smcPriceDao.update(smcPriceDO);
            }


            sqlSession.commit();
        } finally {
            sqlSession.close();
        }
    }
}
