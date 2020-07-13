package com.maikoo.superminercions.service.impl;

import com.maikoo.superminercions.dao.SMCPriceDao;
import com.maikoo.superminercions.dao.SettingDao;
import com.maikoo.superminercions.model.SMCPriceDO;
import com.maikoo.superminercions.model.SettingDO;
import com.maikoo.superminercions.service.SMCPriceService;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.ZoneOffset;

@Service
public class SMCPriceServiceImpl implements SMCPriceService {
    private final static Logger logger = LoggerFactory.getLogger(SMCPriceServiceImpl.class);
    @Autowired
    private SMCPriceDao smcPriceDao;
    @Autowired
    private SettingDao settingDao;

    @Override
    public void updateTodaySMCPrice() {
        LocalDateTime todayDateTime = LocalDate.now().atStartOfDay();
        long todayTimeStamp = todayDateTime.toEpochSecond(ZoneOffset.of("+8"));
        SMCPriceDO smcPriceDO = smcPriceDao.selectOneByDate(todayTimeStamp);

        if (smcPriceDO == null) {
            SettingDO settingDO = settingDao.select();
            smcPriceDO = new SMCPriceDO();
            smcPriceDO.setDate(todayTimeStamp);
            smcPriceDO.setPrice(settingDO.getSmcPrice());
            if(smcPriceDao.insert(smcPriceDO) == 0){
                logger.error("更新今日SMC价格失败");
            }
        }
    }
}
