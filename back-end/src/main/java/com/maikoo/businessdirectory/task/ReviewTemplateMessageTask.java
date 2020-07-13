package com.maikoo.businessdirectory.task;

import com.maikoo.businessdirectory.dao.*;
import com.maikoo.businessdirectory.exception.SystemException;
import com.maikoo.businessdirectory.model.*;
import com.maikoo.businessdirectory.model.constant.DateTimeFormatEnum;
import com.maikoo.businessdirectory.util.TemplateMessageUtil;
import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;
import org.springframework.util.CollectionUtils;

import java.time.Instant;
import java.time.LocalDateTime;
import java.time.ZoneId;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

@Slf4j
@Component
public class ReviewTemplateMessageTask {
    @Autowired
    private SchoolUserApplyDao schoolUserApplyDao;
    @Autowired
    private ClassUserApplyDao classUserApplyDao;
    @Autowired
    private CommunityUserApplyDao communityUserApplyDao;
    @Autowired
    private CountryUserApplyDao countryUserApplyDao;
    @Autowired
    private UserDao userDao;
    @Autowired
    private TemplateMessageUtil templateMessageUtil;

    @Scheduled(cron = "0 0 10,20 * * ? ")
    public void groupUserReview() {
        LocalDateTime localDateTime = LocalDateTime.now();

        LocalDateTime beginDateTime;
        LocalDateTime endDateTime = localDateTime.minusSeconds(localDateTime.getSecond()).minusNanos(localDateTime.getNano());

        int endHour = endDateTime.getHour();
        if (endHour == 10) {
            beginDateTime = endDateTime.minusHours(14);
        } else if (endHour == 20) {
            beginDateTime = endDateTime.minusHours(10);
        } else {
            throw new SystemException("审核通知定时任务时间不匹配");
        }
        long beginTimestamp = localDateTimeToTimestamp(beginDateTime);
        long endTimestamp = localDateTimeToTimestamp(endDateTime);

        List<SchoolUserApplyDO> schoolUserApplyDOList = schoolUserApplyDao.selectNotReviewByDateTime(beginTimestamp, endTimestamp);
        if (!CollectionUtils.isEmpty(schoolUserApplyDOList)) {
            Map<SchoolGroupDO, List<SchoolUserApplyDO>> schoolUserApplyMap = schoolUserApplyDOList.stream().collect(Collectors.groupingBy(SchoolUserApplyDO::getSchoolGroupDO));
            schoolUserApplyMap.forEach((schoolGroupDO, schoolUserApplyDOListByGroup) -> {
                long userId = schoolGroupDO.getUserDO().getUserId();
                UserDO reviewUserDO = userDao.selectOne(userId);
                SchoolUserApplyDO schoolUserApplyDO = schoolUserApplyDOListByGroup.get(0);
                String dateTime = LocalDateTime.
                        ofInstant(Instant.ofEpochSecond(Long.valueOf(schoolUserApplyDO.getAppliedAt())), ZoneId.of("UTC+08:00")).
                        format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                templateMessageUtil.review(reviewUserDO.getOpenid(), schoolUserApplyDO.getName(), schoolGroupDO.getGroupName(), dateTime, schoolUserApplyDOListByGroup.size());
            });
        }

        List<ClassUserApplyDO> classUserApplyDOList = classUserApplyDao.selectNotReviewByDateTime(beginTimestamp, endTimestamp);
        if (!CollectionUtils.isEmpty(classUserApplyDOList)) {
            Map<ClassGroupDO, List<ClassUserApplyDO>> classUserApplyMap = classUserApplyDOList.stream().collect(Collectors.groupingBy(ClassUserApplyDO::getClassGroupDO));
            classUserApplyMap.forEach((classGroupDO, classUserApplyDOListByGroup) -> {
                long userId = classGroupDO.getUserDO().getUserId();
                UserDO reviewUserDO = userDao.selectOne(userId);
                ClassUserApplyDO classUserApplyDO = classUserApplyDOListByGroup.get(0);
                String dateTime = LocalDateTime.
                        ofInstant(Instant.ofEpochSecond(Long.valueOf(classUserApplyDO.getAppliedAt())), ZoneId.of("UTC+08:00")).
                        format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                templateMessageUtil.review(reviewUserDO.getOpenid(), classUserApplyDO.getName(), classGroupDO.getGroupName(), dateTime, classUserApplyDOListByGroup.size());
            });
        }

        List<CommunityUserApplyDO> communityUserApplyDOList = communityUserApplyDao.selectNotReviewByDateTime(beginTimestamp, endTimestamp);
        if (!CollectionUtils.isEmpty(communityUserApplyDOList)) {
            Map<CommunityGroupDO, List<CommunityUserApplyDO>> communityUserApplyMap = communityUserApplyDOList.stream().collect(Collectors.groupingBy(CommunityUserApplyDO::getCommunityGroupDO));
            communityUserApplyMap.forEach((communityGroupDO, communityUserApplyDOListByGroup) -> {
                long userId = communityGroupDO.getUserDO().getUserId();
                UserDO reviewUserDO = userDao.selectOne(userId);
                CommunityUserApplyDO communityUserApplyDO = communityUserApplyDOListByGroup.get(0);
                String dateTime = LocalDateTime.
                        ofInstant(Instant.ofEpochSecond(Long.valueOf(communityUserApplyDO.getAppliedAt())), ZoneId.of("UTC+08:00")).
                        format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                templateMessageUtil.review(reviewUserDO.getOpenid(), communityUserApplyDO.getName(), communityGroupDO.getGroupName(), dateTime, communityUserApplyDOListByGroup.size());
            });
        }

        List<CountryUserApplyDO> countryUserApplyDOList = countryUserApplyDao.selectNotReviewByDateTime(beginTimestamp, endTimestamp);
        if (!CollectionUtils.isEmpty(countryUserApplyDOList)) {
            Map<CountryGroupDO, List<CountryUserApplyDO>> countryUserApplyMap = countryUserApplyDOList.stream().collect(Collectors.groupingBy(CountryUserApplyDO::getCountryGroupDO));
            countryUserApplyMap.forEach((countryGroupDO, countryUserApplyDOListByGroup) -> {
                long userId = countryGroupDO.getUserDO().getUserId();
                UserDO reviewUserDO = userDao.selectOne(userId);
                CountryUserApplyDO countryUserApplyDO = countryUserApplyDOListByGroup.get(0);
                String dateTime = LocalDateTime.
                        ofInstant(Instant.ofEpochSecond(Long.valueOf(countryUserApplyDO.getAppliedAt())), ZoneId.of("UTC+08:00")).
                        format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                templateMessageUtil.review(reviewUserDO.getOpenid(), countryUserApplyDO.getName(), countryGroupDO.getGroupName(), dateTime, countryUserApplyDOListByGroup.size());
            });
        }
    }

    /**
     * 当前时间转换成时间戳（秒）
     *
     * @param localDateTime 当前时间
     * @return 时间戳（秒）
     */
    private long localDateTimeToTimestamp(LocalDateTime localDateTime) {
        return localDateTime.atZone(ZoneId.of("UTC+08:00")).toInstant().toEpochMilli() / 1000;
    }
}
