package com.maikoo.superminercions.service.impl;

import com.maikoo.superminercions.dao.NewsDao;
import com.maikoo.superminercions.dao.NoticeDao;
import com.maikoo.superminercions.dao.SMCPriceDao;
import com.maikoo.superminercions.dao.SlideshowDao;
import com.maikoo.superminercions.model.NoticeDO;
import com.maikoo.superminercions.model.SMCPriceDO;
import com.maikoo.superminercions.model.SlideshowDO;
import com.maikoo.superminercions.model.dto.HomeDTO;
import com.maikoo.superminercions.model.dto.HomeSlideshowDTO;
import com.maikoo.superminercions.model.dto.NoticeDTO;
import com.maikoo.superminercions.service.HomeService;
import com.maikoo.superminercions.util.TimeUtil;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.math.BigDecimal;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.Period;
import java.time.ZoneOffset;
import java.util.ArrayList;
import java.util.List;

@Service
public class HomeServiceImpl implements HomeService {
    @Autowired
    private NewsDao newsDao;
    @Autowired
    private NoticeDao noticeDao;
    @Autowired
    private SlideshowDao slideshowDao;
    @Autowired
    private SMCPriceDao smcPriceDao;

    @Override
    public HomeDTO home() {
        HomeDTO homeDTO = new HomeDTO();

        List<SlideshowDO> slideshowDOList = slideshowDao.selectByEnabled();
        if (slideshowDOList != null && slideshowDOList.size() > 0) {
            List<HomeSlideshowDTO> homeSlideshowDTOList = new ArrayList<>();
            slideshowDOList.forEach(slideshowDO -> homeSlideshowDTOList.add(HomeSlideshowDTO.valueOf(slideshowDO)));
            homeDTO.setBanner(homeSlideshowDTOList);
        } else {
            homeDTO.setBanner(new ArrayList<>());
        }

//        NewsDO newsDO = newsDao.selectLastOne();
//        homeDTO.setNotice(NoticeDTO.valueOf(newsDO));

        NoticeDO noticeDO = noticeDao.selectLastOne();
        homeDTO.setNotice(NoticeDTO.valueOf(noticeDO));

        LocalDateTime beginDateTime = LocalDate.now().atStartOfDay().minusDays(9);
        LocalDateTime endDateTime = LocalDate.now().atStartOfDay();
        long beginTimeStamp = beginDateTime.toEpochSecond(ZoneOffset.of("+8"));
        long endTimeStamp = endDateTime.toEpochSecond(ZoneOffset.of("+8"));
        List<SMCPriceDO> smcPriceDOList = smcPriceDao.selectByDate(beginTimeStamp, endTimeStamp);
        List<BigDecimal> smcPriceArray = new ArrayList<>(10);
        BigDecimal zero = new BigDecimal(0.00);
        for (int i = 0; i < 10; i++) {
            smcPriceArray.add(zero);
        }
        if (smcPriceDOList != null && smcPriceDOList.size() > 0) {
            smcPriceDOList.forEach(smcPriceDO -> {
                LocalDateTime localDateTime = TimeUtil.timeStampToDateTime(smcPriceDO.getDate());
                Period period = Period.between(beginDateTime.toLocalDate(), localDateTime.toLocalDate());
                smcPriceArray.set(period.getDays(), smcPriceDO.getPrice());
            });
        }
        homeDTO.setChartsData(smcPriceArray);
        homeDTO.setTodaySmcPrice(smcPriceArray.get(9));

        return homeDTO;
    }
}
