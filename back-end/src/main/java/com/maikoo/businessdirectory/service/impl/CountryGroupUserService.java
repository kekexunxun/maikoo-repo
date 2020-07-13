package com.maikoo.businessdirectory.service.impl;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.github.pagehelper.PageHelper;
import com.maikoo.businessdirectory.config.CustomEnvironmentConfig;
import com.maikoo.businessdirectory.dao.*;
import com.maikoo.businessdirectory.model.*;
import com.maikoo.businessdirectory.model.constant.*;
import com.maikoo.businessdirectory.model.dto.AdminGroupUserDTO;
import com.maikoo.businessdirectory.model.dto.FileDTO;
import com.maikoo.businessdirectory.model.dto.GroupUserDTO;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;
import com.maikoo.businessdirectory.service.GroupUserService;
import com.maikoo.businessdirectory.util.FileUtil;
import com.maikoo.businessdirectory.util.RedisUtil;
import com.maikoo.businessdirectory.util.TemplateMessageUtil;
import com.maikoo.businessdirectory.util.WechatUtil;
import lombok.extern.slf4j.Slf4j;
import org.apache.poi.hssf.usermodel.HSSFWorkbook;
import org.apache.poi.ss.usermodel.CreationHelper;
import org.apache.poi.ss.usermodel.Row;
import org.apache.poi.ss.usermodel.Sheet;
import org.apache.poi.ss.usermodel.Workbook;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.util.CollectionUtils;

import javax.servlet.http.HttpSession;
import java.io.FileOutputStream;
import java.io.OutputStream;
import java.security.InvalidParameterException;
import java.time.Instant;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.ZoneId;
import java.util.ArrayList;
import java.util.List;
import java.util.stream.IntStream;

@Slf4j
@Service
public class CountryGroupUserService implements GroupUserService {
    @Autowired
    private CountryUserDao countryUserDao;

    @Autowired
    private CountryGroupDao countryGroupDao;

    @Autowired
    private CountryUserFavDao countryUserFavDao;

    @Autowired
    private CountryUserApplyDao countryUserApplyDao;

    @Autowired
    private UserDao userDao;

    @Autowired
    private FormIdDao formIdDao;

    @Autowired
    private WechatUtil wechatUtil;

    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;

    @Autowired
    private FileUtil fileUtil;

    @Autowired
    private RedisUtil redisUtil;

    @Autowired
    private TemplateMessageUtil templateMessageUtil;

    @Autowired
    private HttpSession session;

    @Autowired
    private ObjectMapper objectMapper;

    @Autowired
    private MessageDao messageDao;

    @Override
    public void save(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        CountryUserDO currentCountyUserDO = countryUserDao.selectByGroupIdAndUserId(groupId, userId);

        switch (groupUserQuery.getAction()) {
            case FILL:
                groupAdminSave(groupUserQuery, currentUserDO, currentCountyUserDO);
                break;
            case UPDATE:
                groupUserSave(groupUserQuery, currentUserDO, currentCountyUserDO);
                break;
            case APPLY:
                applyGroupUserSave(groupUserQuery, currentUserDO, currentCountyUserDO);
                break;
        }
    }

    /**
     * 信息更新（群管理员）
     * 创建群，并新增群管理员信息
     *
     * @param groupUserQuery
     * @param currentUserDO
     */
    private void groupAdminSave(GroupUserQuery groupUserQuery, UserDO currentUserDO, CountryUserDO currentCountyUserDO) {
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        if (currentCountyUserDO != null) {
            log.info("群管理员已存在。group id: {}, user id: {}", groupId, userId);
            throw new InvalidParameterException();
        }

        String key = groupUserQuery.getGroupType() + "_" + currentUserDO.getUserId() + "_" + groupUserQuery.getGroupId();

        CountryGroupDO countryGroupDO = (CountryGroupDO) redisUtil.value(key);
        countryGroupDao.insert(countryGroupDO);

        CountryUserDO countryUserDO = CountryUserDO.valueOf(groupUserQuery);
        countryUserDO.setCountryGroupDO(countryGroupDO);
        countryUserDO.setUserDO(currentUserDO);
        int insert = countryUserDao.insert(countryUserDO);

        if (insert == 1) {
            redisUtil.delete(key);
        }
    }

    /**
     * 信息更新（群成员）
     *
     * @param groupUserQuery
     * @param currentUserDO
     */
    private void groupUserSave(GroupUserQuery groupUserQuery, UserDO currentUserDO, CountryUserDO currentCountyUserDO) {
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        if (currentCountyUserDO == null) {
            log.info("群成员不存在。group id: {}, user id: {}", groupId, userId);
            throw new InvalidParameterException();
        }

        CountryUserDO newCountryUserDO = CountryUserDO.valueOf(groupUserQuery);
        newCountryUserDO.setIdx(currentCountyUserDO.getIdx());
        countryUserDao.update(newCountryUserDO);
    }

    /**
     * 信息更新（申请成员）
     * 新增申请信息
     *
     * @param groupUserQuery
     * @param currentUserDO
     */
    private void applyGroupUserSave(GroupUserQuery groupUserQuery, UserDO currentUserDO, CountryUserDO currentCountyUserDO) {
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        Long notReviewApplyId = countryUserApplyDao.selectNotReviewedApplyByGroupIdAndUserId(groupId, userId);

        if (currentCountyUserDO != null || notReviewApplyId != null) {
            log.info("群成员已存在或者申请记录已存在且未审核。group id: {}, user id: {}", groupId, userId);
            throw new InvalidParameterException();
        }

        CountryUserApplyDO countryUserApplyDO = CountryUserApplyDO.valueOf(groupUserQuery);
        countryUserApplyDO.setUserDO(currentUserDO);
        countryUserApplyDao.insert(countryUserApplyDO);
    }

    @Override
    public void remove(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        if (countryGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), groupUserQuery.getGroupId()) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }

        if (currentUserDO.getUserId() == groupUserQuery.getUserId()) {
            //TODO 群管理员自己不能删除自己
            throw new InvalidParameterException("管理员不能删除自己");
        }

        CountryUserDO processedRemoveCountryUserDO = new CountryUserDO();
        processedRemoveCountryUserDO.setUserDO(currentUserDO);

        UserDO userDO = new UserDO();
        userDO.setUserId(groupUserQuery.getUserId());

        CountryGroupDO countryGroupDO = new CountryGroupDO();
        countryGroupDO.setGroupId(groupUserQuery.getGroupId());

        CountryUserDO countryUserDO = new CountryUserDO();
        countryUserDO.setUserDO(userDO);
        countryUserDO.setCountryGroupDO(countryGroupDO);
        countryUserDO.setProcessedRemoveCountryUserDO(processedRemoveCountryUserDO);
        String userName = countryUserDao.getUserName(groupUserQuery.getUserId(), groupUserQuery.getGroupId());
        int count = countryUserDao.delete(countryUserDO);
        if (count > 0) {
            //添加站内消息
            String groupName = countryGroupDao.getGroupName(groupUserQuery.getGroupId());
            List<MessageDO> messageDOList = new ArrayList<>();
            MessageDO messageDO = new MessageDO();
            messageDO.setMsgContent("您已被移出群「" + groupName + "」，您将无法进入该群");
            messageDO.setMsgTitle("系统通知");
            messageDO.setSendTo(groupUserQuery.getUserId());
            messageDOList.add(messageDO);

            MessageDO messageDO2 = new MessageDO();
            messageDO2.setMsgContent("您已将「" + userName + "」移出群「" + groupName + "」");
            messageDO2.setMsgTitle("系统通知");
            messageDO2.setSendTo(currentUserDO.getUserId());
            messageDOList.add(messageDO2);

            messageDao.createMessage(messageDOList);
        }


    }

    @Override
    public void insertFavor(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        CountryUserDO countryUserDO = countryUserDao.selectByGroupIdAndUserId(groupUserQuery.getGroupId(), groupUserQuery.getUserId());

        if (countryUserDO != null) {
            CountryUserFavDO countryUserFavDO = new CountryUserFavDO();
            countryUserFavDO.setUserId(currentUserDO.getUserId());
            countryUserFavDO.setCountryUserDO(countryUserDO);
            countryUserFavDao.insert(countryUserFavDO);
        }
    }

    @Override
    public void removeFavor(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        CountryUserDO countryUserDO = countryUserDao.selectByGroupIdAndUserId(groupUserQuery.getGroupId(), groupUserQuery.getUserId());

        if (countryUserDO != null) {
            CountryUserFavDO countryUserFavDO = new CountryUserFavDO();
            countryUserFavDO.setUserId(currentUserDO.getUserId());
            countryUserFavDO.setCountryUserDO(countryUserDO);
            countryUserFavDao.deleteByUserIdAndClassUserIdx(countryUserFavDO);
        }
    }

    @Override
    public void updateReview(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        CountryUserApplyDO countryUserApplyDO = countryUserApplyDao.selectByApplyId(groupUserQuery.getApplyId());

        if (countryGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), countryUserApplyDO.getCountryGroupDO().getGroupId()) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }

        if (countryUserApplyDO != null && ReviewStatusEnum.intStatusToEnum(countryUserApplyDO.getStatus()) == ReviewStatusEnum.PENDING) {
            CountryUserApplyDO newCountryUserApplyDO = new CountryUserApplyDO();
            newCountryUserApplyDO.setProcessedUserId(currentUserDO.getUserId());
            newCountryUserApplyDO.setStatus(groupUserQuery.getResult().getIntStatus());
            newCountryUserApplyDO.setIdx(groupUserQuery.getApplyId());
            countryUserApplyDao.updateStatus(newCountryUserApplyDO);

            if (groupUserQuery.getResult() == ReviewStatusEnum.APPROVE) {
                CountryUserDO countryUserDO = new CountryUserDO();
                countryUserDO.setUserDO(countryUserApplyDO.getUserDO());
                countryUserDO.setName(countryUserApplyDO.getName());
                countryUserDO.setGender(countryUserApplyDO.getGender());
                countryUserDO.setMobile(countryUserApplyDO.getMobile());
                countryUserDO.setPosition(countryUserApplyDO.getPosition());
                countryUserDO.setCompany(countryUserApplyDO.getCompany());
                countryUserDO.setMobile(countryUserApplyDO.getMobile());
                countryUserDO.setBrief(countryUserApplyDO.getBrief());
                countryUserDO.setTag(countryUserApplyDO.getTag());
                countryUserDO.setCountryGroupDO(countryUserApplyDO.getCountryGroupDO());

                countryUserDao.insert(countryUserDO);
            }

            Thread thread = new Thread(new Runnable() {
                @Override
                public void run() {
                    long groupId = countryUserApplyDO.getCountryGroupDO().getGroupId();
                    long userId = countryUserApplyDO.getUserDO().getUserId();

                    List<Long> ids = countryUserDao.selectIdsByGroupId(groupId);
//                    CountryUserDO processedCountryUserDO = countryUserDao.selectByGroupIdAndUserId(groupId, userId);
                    UserDO applyUserDO = userDao.selectOne(userId);
                    String dateTime = LocalDateTime.
                            ofInstant(Instant.ofEpochSecond(Long.valueOf(countryUserApplyDO.getAppliedAt())), ZoneId.of("UTC+08:00")).
                            format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());

                    templateMessageUtil.applyResult(GroupTypeEnum.COUNTRY, applyUserDO, groupUserQuery.getApplyId(), groupId, countryUserApplyDO.getCountryGroupDO().getGroupName(), countryUserApplyDO.getBrief(), dateTime, groupUserQuery.getResult());
                }
            });
            thread.start();
        }
    }

    @Override
    public GroupUserDTO information(GroupUserQuery groupUserQuery) {
        GroupUserDTO groupUserDTO = null;
        CountryUserDO countryUserDO = countryUserDao.selectByGroupIdAndUserId(groupUserQuery.getGroupId(), groupUserQuery.getUserId());
        if (countryUserDO != null) {
            groupUserDTO = GroupUserDTO.valueOf(countryUserDO);

            UserDO userDO = (UserDO) session.getAttribute("current_user");
            if (userDO.getUserId() != groupUserQuery.getUserId()) {
                CountryUserFavDO countryUserFavDO = countryUserFavDao.selectByGroupUserIdAndUserId(countryUserDO.getIdx(), userDO.getUserId());
                if (countryUserFavDO != null) {
                    groupUserDTO.setFav(true);
                }
            }
        }
        return groupUserDTO;
    }


    @Override
    public List<GroupUserDTO> favorList(int pageNumber) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = countryUserFavDao.selectIdsByUserId(currentUserDO.getUserId());
        if (!CollectionUtils.isEmpty(ids)) {
            List<CountryUserFavDO> countryUserFavDOList = countryUserFavDao.selectByIds(ids);
            countryUserFavDOList.forEach(countryUserFavDO -> groupUserDTOList.add(GroupUserDTO.valueOf(countryUserFavDO)));
        }
        return groupUserDTOList;
    }

    @Override
    public List<GroupUserDTO> selectByGroup(GroupUserQuery groupUserQuery) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        PageHelper.startPage(groupUserQuery.getPageNum() > 0 ? groupUserQuery.getPageNum() : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = countryUserDao.selectIdsByGroupId(groupUserQuery.getGroupId());
        if (!CollectionUtils.isEmpty(ids)) {
            List<CountryUserDO> countryUserDOList = null;
            if (groupUserQuery.isHasDetail()) {
                countryUserDOList = countryUserDao.selectInformationByIds(ids);
            } else {
                countryUserDOList = countryUserDao.selectByIds(ids);
            }
            countryUserDOList.forEach(countryUserDO -> groupUserDTOList.add(GroupUserDTO.valueOf(countryUserDO)));
        }

        return groupUserDTOList;

    }

    @Override
    public List<AdminGroupUserDTO> getUserListByGroupId(long groupId) {
        List<AdminGroupUserDTO> adminGroupUserDTOList = new ArrayList<>();
        List<CountryUserDO> countryUserDOList = countryUserDao.selectUserInformationListByGroupId(groupId);
        if (!CollectionUtils.isEmpty(countryUserDOList)) {
            countryUserDOList.forEach(countryUserDO -> adminGroupUserDTOList.add(AdminGroupUserDTO.valueOf(countryUserDO)));
        }
        return adminGroupUserDTOList;
    }


    @Override
    public List<GroupUserDTO> userApplyList(int pageNumber) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = countryUserApplyDao.selectIdsByUserId(currentUserDO.getUserId());
        return applyListByIds(ids);
    }

    private List<GroupUserDTO> applyListByIds(List<Long> ids) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        if (!CollectionUtils.isEmpty(ids)) {
            List<CountryUserApplyDO> countryUserApplyDOS = countryUserApplyDao.selectApplyRecordByIds(ids);
            countryUserApplyDOS.forEach(countryUserApplyDO -> groupUserDTOList.add(GroupUserDTO.valueOf(countryUserApplyDO)));
        }
        return groupUserDTOList;
    }

    @Override
    public List<GroupUserDTO> adminApplyList(int pageNumber) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<CountryUserApplyDO> countryUserApplyDOList = countryUserApplyDao.selectReviewRecordByGroupUserId(currentUserDO.getUserId());
        if (!CollectionUtils.isEmpty(countryUserApplyDOList)) {
            countryUserApplyDOList.forEach(countryUserApplyDO -> groupUserDTOList.add(GroupUserDTO.valueOf(countryUserApplyDO)));
        }
        return groupUserDTOList;
    }

    @Override
    public GroupUserDTO userInformation(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        if (currentUserDO.getUserId() == groupUserQuery.getUserId()) {
            throw new InvalidParameterException("用户不匹配");
        }

        return applyInformation(groupUserQuery);
    }

    private GroupUserDTO applyInformation(GroupUserQuery groupUserQuery) {
        GroupUserDTO groupUserDTO = null;
        CountryUserApplyDO countryUserApplyDO = countryUserApplyDao.selectByApplyId(groupUserQuery.getApplyId());
        if (countryUserApplyDO != null) {
            groupUserDTO = GroupUserDTO.valueOf(countryUserApplyDO);
            groupUserDTO.setReviewStatus(ReviewStatusEnum.intStatusToEnum(countryUserApplyDO.getStatus()).toString());
        }
        return groupUserDTO;
    }

    @Override
    public GroupUserDTO adminInformation(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        CountryUserApplyDO countryUserApplyDO = countryUserApplyDao.selectGroupIdByApplyId(groupUserQuery.getApplyId());
        if (countryUserApplyDO == null) {
            throw new InvalidParameterException("没有申请ApplyId");
        }
        if (countryGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), countryUserApplyDO.getCountryGroupDO().getGroupId()) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }

        return applyInformation(groupUserQuery);
    }


    @Override
    public FileDTO userExcel(long groupId) {
        FileDTO fileDTO = null;
        List<CountryUserDO> countryUserDOList = countryUserDao.selectUserListInfoExportExcel(groupId);

        if (!CollectionUtils.isEmpty(countryUserDOList)) {
            Workbook wb = new HSSFWorkbook();
            CreationHelper createHelper = wb.getCreationHelper();
            Sheet sheet = wb.createSheet("new sheet");

            Row baseRow = sheet.createRow(0);

            baseRow.createCell(0).setCellValue("ID");
            baseRow.createCell(1).setCellValue("姓名");
            baseRow.createCell(2).setCellValue("权限");
            baseRow.createCell(3).setCellValue("性别");
            baseRow.createCell(4).setCellValue("年龄段");
            baseRow.createCell(5).setCellValue("手机号");
            baseRow.createCell(6).setCellValue("公司名称");
            baseRow.createCell(7).setCellValue("职位");
            baseRow.createCell(8).setCellValue("个人简介");
            baseRow.createCell(9).setCellValue("加入时间");
            baseRow.createCell(10).setCellValue("退出时间");
            baseRow.createCell(11).setCellValue("状态");

            IntStream.range(0, countryUserDOList.size()).forEach(idx -> {
                Row row = sheet.createRow(idx + 1);

                CountryUserDO countryUserDO = countryUserDOList.get(idx);
                String joinAt = LocalDateTime.ofInstant(
                        Instant.ofEpochSecond(Long.valueOf(countryUserDO.getJoinedAt())), ZoneId.of("UTC+08:00")).
                        format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                String quiteAt = "";
                if (countryUserDO.getQuitedAt() != 0) {
                    quiteAt = LocalDateTime.ofInstant(
                            Instant.ofEpochSecond(Long.valueOf(countryUserDO.getQuitedAt())), ZoneId.of("UTC+08:00")).
                            format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                }

                String role = null;
                if (countryUserDO.getCountryGroupDO() != null) {
                    if (countryUserDO.getCountryGroupDO().getUserDO() != null) {
                        if (countryUserDO.getUserDO().getUserId() == countryUserDO.getCountryGroupDO().getUserDO().getUserId()) {
                            role = UserRoleEnum.ADMIN.getStringStatus();
                        } else {
                            role = UserRoleEnum.MEMBER.getStringStatus();
                        }
                    }
                }
                row.createCell(0).setCellValue(countryUserDO.getUserDO().getUserId());
                row.createCell(1).setCellValue(countryUserDO.getName());
                row.createCell(2).setCellValue(role);
                row.createCell(3).setCellValue(countryUserDO.getGender() == 1 ? GenderEnum.MAN.getStringStatus() : GenderEnum.WOMAN.getStringStatus());
                row.createCell(4).setCellValue(countryUserDO.getTag());
                row.createCell(5).setCellValue(countryUserDO.getMobile());
                row.createCell(6).setCellValue(countryUserDO.getCompany());
                row.createCell(7).setCellValue(countryUserDO.getPosition());
                row.createCell(8).setCellValue(countryUserDO.getBrief());
                row.createCell(9).setCellValue(joinAt);
                row.createCell(10).setCellValue(quiteAt);
                row.createCell(11).setCellValue(countryUserDO.getStatus() == 1 ? UserStatusEnum.ENABLE.getStringStatus() : UserStatusEnum.DISABLE.getStringStatus());
            });
            LocalDate localDate = LocalDate.now();
            String basePath = customEnvironmentConfig.getUploadLocation() + customEnvironmentConfig.getExcelLocation();
            String filename = fileUtil.filename(basePath, "xls", localDate.format(DateTimeFormatEnum.COMMON_DATE.getDateTimeFormatter()) + "-" + countryUserDOList.get(0).getCountryGroupDO().getGroupName());
            try (OutputStream fileOut = new FileOutputStream(basePath + filename)) {
                wb.write(fileOut);
            } catch (Exception e) {
                throw new RuntimeException(e);
            }

            fileDTO = new FileDTO();
            fileDTO.setFileUrl("/" + customEnvironmentConfig.getExcelLocation() + filename);
        }

        return fileDTO;
    }

    @Override
    public GroupUserDTO userInfoByAdmin(GroupUserQuery groupUserQuery) {
        CountryUserDO countryUserDO = countryUserDao.userInfoAdmin(groupUserQuery.getUserId(), groupUserQuery.getGroupId());
        GroupUserDTO groupUserDTO = GroupUserDTO.valueOf(countryUserDO);
        return groupUserDTO;
    }

    @Override
    public List<GroupUserDTO> searchUserInfo(GroupUserQuery groupUserQuery) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");

        //先判断用户是不是群用户
        CountryUserDO countryUserDO2 = countryUserDao.selectIdxByGroupIdAndUserId(groupUserQuery.getGroupId(), userDO.getUserId());
        if (countryUserDO2 != null) {
            List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
            CountryUserDO countryUserDO = CountryUserDO.valueOf(groupUserQuery);
            List<CountryUserDO> countryUserDOList = countryUserDao.searchUserInfo(countryUserDO);
            countryUserDOList.forEach(countryUserDO1 -> groupUserDTOList.add(GroupUserDTO.valueOf(countryUserDO1)));
            return groupUserDTOList;
        } else {
            throw new InvalidParameterException("当前用户不是当前群的用户");
        }
    }

    @Override
    public GroupUserDTO isMember(GroupUserQuery groupUserQuery) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        GroupUserDTO groupUserDTO = new GroupUserDTO();
        CountryUserDO countryUserDO = countryUserDao.selectIdxByGroupIdAndUserId(groupUserQuery.getGroupId(), userDO.getUserId());
        if (countryUserDO != null) {
            groupUserDTO.setMember(true);
        }
        return groupUserDTO;
    }

    @Override
    public GroupUserDTO isApply(GroupUserQuery groupUserQuery) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        GroupUserDTO groupUserDTO = new GroupUserDTO();
        CountryUserApplyDO countryUserApplyDO = countryUserApplyDao.isApplyUser(userDO.getUserId(), groupUserQuery.getGroupId());
        if (countryUserApplyDO != null) {
            groupUserDTO = GroupUserDTO.valueOf(countryUserApplyDO);
            groupUserDTO.setHasApplied(true);
        }
        return groupUserDTO;
    }
}
