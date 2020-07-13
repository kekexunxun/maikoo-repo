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
public class SchoolGroupUserService implements GroupUserService {
    @Autowired
    private SchoolUserDao schoolUserDao;

    @Autowired
    private SchoolUserFavDao schoolUserFavDao;

    @Autowired
    private SchoolGroupDao schoolGroupDao;

    @Autowired
    private SchoolUserApplyDao schoolUserApplyDao;

    @Autowired
    private UserDao userDao;

    @Autowired
    private FormIdDao formIdDao;

    @Autowired
    private WechatUtil wechatUtil;

    @Autowired
    private FileUtil fileUtil;

    @Autowired
    private RedisUtil redisUtil;

    @Autowired
    private TemplateMessageUtil templateMessageUtil;

    @Autowired
    private HttpSession session;

    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;

    @Autowired
    private ObjectMapper objectMapper;

    @Autowired
    private MessageDao messageDao;

    @Override
    public void save(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        SchoolUserDO currentSchoolUserDO = schoolUserDao.selectByGroupIdAndUserId(groupId, userId);

        switch (groupUserQuery.getAction()) {
            case FILL:
                groupAdminSave(groupUserQuery, currentUserDO, currentSchoolUserDO);
                break;
            case UPDATE:
                groupUserSave(groupUserQuery, currentUserDO, currentSchoolUserDO);
                break;
            case APPLY:
                applyGroupUserSave(groupUserQuery, currentUserDO, currentSchoolUserDO);
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
    private void groupAdminSave(GroupUserQuery groupUserQuery, UserDO currentUserDO, SchoolUserDO currentSchoolUserDO) {
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        if (currentSchoolUserDO != null) {
            log.info("群管理员已存在。group id: {}, user id: {}", groupId, userId);
            throw new InvalidParameterException();
        }

        String key = groupUserQuery.getGroupType() + "_" + currentUserDO.getUserId() + "_" + groupUserQuery.getGroupId();

        SchoolGroupDO schoolGroupDO = (SchoolGroupDO) redisUtil.value(key);
        schoolGroupDao.insert(schoolGroupDO);

        SchoolUserDO schoolUserDO = SchoolUserDO.valueOf(groupUserQuery);
        schoolUserDO.setSchoolGroupDO(schoolGroupDO);
        schoolUserDO.setUserDO(currentUserDO);
        int insert = schoolUserDao.insert(schoolUserDO);

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
    private void groupUserSave(GroupUserQuery groupUserQuery, UserDO currentUserDO, SchoolUserDO currentSchoolUserDO) {
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        if (currentSchoolUserDO == null) {
            log.info("群成员不存在。group id: {}, user id: {}", groupId, userId);
            throw new InvalidParameterException();
        }

        SchoolUserDO newSchoolUserDO = SchoolUserDO.valueOf(groupUserQuery);
        newSchoolUserDO.setIdx(currentSchoolUserDO.getIdx());
        schoolUserDao.update(newSchoolUserDO);
    }

    /**
     * 信息更新（申请成员）
     * 新增申请信息
     *
     * @param groupUserQuery
     * @param currentUserDO
     */
    private void applyGroupUserSave(GroupUserQuery groupUserQuery, UserDO currentUserDO, SchoolUserDO currentSchoolUserDO) {
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        Long notReviewApplyId = schoolUserApplyDao.selectNotReviewedApplyByGroupIdAndUserId(groupId, userId);

        if (currentSchoolUserDO != null || notReviewApplyId != null) {
            log.info("群成员已存在或者申请记录已存在且未审核。group id: {}, user id: {}", groupId, userId);
            throw new InvalidParameterException();
        }

        SchoolUserApplyDO schoolUserApplyDO = SchoolUserApplyDO.valueOf(groupUserQuery);
        schoolUserApplyDO.setUserDO(currentUserDO);
        schoolUserApplyDao.insert(schoolUserApplyDO);
    }

    @Override
    public void remove(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        if (schoolGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), groupUserQuery.getGroupId()) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }

        if (currentUserDO.getUserId() == groupUserQuery.getUserId()) {
            //TODO 群管理员自己不能删除自己
            throw new InvalidParameterException("群管理员自己不能删除自己");
        }
        SchoolUserDO processedRemoveSchoolUserDO = new SchoolUserDO();
        processedRemoveSchoolUserDO.setUserDO(currentUserDO);

        UserDO userDO = new UserDO();
        userDO.setUserId(groupUserQuery.getUserId());

        SchoolGroupDO schoolGroupDO = new SchoolGroupDO();
        schoolGroupDO.setGroupId(groupUserQuery.getGroupId());

        SchoolUserDO schoolUserDO = new SchoolUserDO();
        schoolUserDO.setUserDO(userDO);
        schoolUserDO.setSchoolGroupDO(schoolGroupDO);
        schoolUserDO.setProcessedRemoveSchoolUserDO(processedRemoveSchoolUserDO);
        String userName = schoolUserDao.getUserName(groupUserQuery.getUserId(), groupUserQuery.getGroupId());
        int count = schoolUserDao.delete(schoolUserDO);
        if (count > 0) {
            //添加站内消息
            String groupName = schoolGroupDao.getGroupName(groupUserQuery.getGroupId());
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

        SchoolUserDO schoolUserDO = schoolUserDao.selectByGroupIdAndUserId(groupUserQuery.getGroupId(), groupUserQuery.getUserId());

        if (schoolUserDO != null) {
            SchoolUserFavDO schoolUserFavDO = new SchoolUserFavDO();
            schoolUserFavDO.setUserId(currentUserDO.getUserId());
            schoolUserFavDO.setSchoolUserDO(schoolUserDO);
            schoolUserFavDao.insert(schoolUserFavDO);
        }
    }

    @Override
    public void removeFavor(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        SchoolUserDO schoolUserDO = schoolUserDao.selectByGroupIdAndUserId(groupUserQuery.getGroupId(), groupUserQuery.getUserId());

        if (schoolUserDO != null) {
            SchoolUserFavDO schoolUserFavDO = new SchoolUserFavDO();
            schoolUserFavDO.setUserId(currentUserDO.getUserId());
            schoolUserFavDO.setSchoolUserDO(schoolUserDO);
            schoolUserFavDao.deleteByUserIdAndClassUserIdx(schoolUserFavDO);
        }
    }

    @Override
    public void updateReview(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        SchoolUserApplyDO schoolUserApplyDO = schoolUserApplyDao.selectByApplyId(groupUserQuery.getApplyId());

        if (schoolGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), schoolUserApplyDO.getSchoolGroupDO().getGroupId()) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }

        if (schoolUserApplyDO != null && ReviewStatusEnum.intStatusToEnum(schoolUserApplyDO.getStatus()) == ReviewStatusEnum.PENDING) {
            SchoolUserApplyDO newSchoolUserApplyDO = new SchoolUserApplyDO();
            newSchoolUserApplyDO.setProcessedUserId(currentUserDO.getUserId());
            newSchoolUserApplyDO.setStatus(groupUserQuery.getResult().getIntStatus());
            newSchoolUserApplyDO.setIdx(groupUserQuery.getApplyId());
            schoolUserApplyDao.updateStatus(newSchoolUserApplyDO);

            if (groupUserQuery.getResult() == ReviewStatusEnum.APPROVE) {
                SchoolUserDO schoolUserDO = new SchoolUserDO();
                schoolUserDO.setUserDO(schoolUserApplyDO.getUserDO());
                schoolUserDO.setName(schoolUserApplyDO.getName());
                schoolUserDO.setGender(schoolUserApplyDO.getGender());
                schoolUserDO.setMobile(schoolUserApplyDO.getMobile());
                schoolUserDO.setCompany(schoolUserApplyDO.getCompany());
                schoolUserDO.setPosition(schoolUserApplyDO.getPosition());
                schoolUserDO.setBrief(schoolUserApplyDO.getBrief());
                schoolUserDO.setType(schoolUserApplyDO.getType());
                schoolUserDO.setGraduatedAt(schoolUserApplyDO.getGraduatedAt());
                schoolUserDO.setSchoolGroupDO(schoolUserApplyDO.getSchoolGroupDO());

                schoolUserDao.insert(schoolUserDO);
            }

            Thread thread = new Thread(new Runnable() {
                @Override
                public void run() {
                    long groupId = schoolUserApplyDO.getSchoolGroupDO().getGroupId();
                    long userId = schoolUserApplyDO.getUserDO().getUserId();

                    List<Long> ids = schoolUserDao.selectIdsByGroupId(groupId);
//                    SchoolUserDO processedSchoolUserDO = schoolUserDao.selectByGroupIdAndUserId(groupId, currentUserDO.getUserId());
                    UserDO applyUserDO = userDao.selectOne(userId);
                    String dateTime = LocalDateTime.
                            ofInstant(Instant.ofEpochSecond(Long.valueOf(schoolUserApplyDO.getAppliedAt())), ZoneId.of("UTC+08:00")).
                            format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());

                    templateMessageUtil.applyResult(GroupTypeEnum.SCHOOL, applyUserDO, groupUserQuery.getApplyId(), groupId, schoolUserApplyDO.getSchoolGroupDO().getGroupName(), schoolUserApplyDO.getBrief(), dateTime, groupUserQuery.getResult());
                }
            });
            thread.start();
        }
    }


    @Override
    public GroupUserDTO information(GroupUserQuery groupUserQuery) {
        GroupUserDTO groupUserDTO = null;
        SchoolUserDO schoolUserDO = schoolUserDao.selectByGroupIdAndUserId(groupUserQuery.getGroupId(), groupUserQuery.getUserId());
        UserDO userDO = (UserDO) session.getAttribute("current_user");

        if (schoolUserDO != null) {
            groupUserDTO = GroupUserDTO.valueOf(schoolUserDO);
            if (groupUserQuery.getUserId() != userDO.getUserId()) {
                SchoolUserFavDO schoolUserFavDO = schoolUserFavDao.selectByGroupUserIdAndUserId(schoolUserDO.getIdx(), userDO.getUserId());
                if (schoolUserFavDO != null) {
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
        List<Long> ids = schoolUserFavDao.selectIdsByUserId(currentUserDO.getUserId());
        if (!CollectionUtils.isEmpty(ids)) {
            List<SchoolUserFavDO> schoolUserFavDOList = schoolUserFavDao.selectByIds(ids);
            schoolUserFavDOList.forEach(schoolUserFavDO -> groupUserDTOList.add(GroupUserDTO.valueOf(schoolUserFavDO)));
        }
        return groupUserDTOList;
    }

    @Override
    public List<GroupUserDTO> selectByGroup(GroupUserQuery groupUserQuery) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        PageHelper.startPage(groupUserQuery.getPageNum() > 0 ? groupUserQuery.getPageNum() : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = schoolUserDao.selectIdsByGroupId(groupUserQuery.getGroupId());
        if (!CollectionUtils.isEmpty(ids)) {
            List<SchoolUserDO> schoolUserDOList = null;
            if (groupUserQuery.isHasDetail()) {
                schoolUserDOList = schoolUserDao.selectInformationByIds(ids);
            } else {
                schoolUserDOList = schoolUserDao.selectByIds(ids);
            }
            schoolUserDOList.forEach(schoolUserDO -> groupUserDTOList.add(GroupUserDTO.valueOf(schoolUserDO)));
        }

        return groupUserDTOList;
    }

    @Override
    public List<AdminGroupUserDTO> getUserListByGroupId(long groupId) {
        List<AdminGroupUserDTO> adminGroupUserDTOList = new ArrayList<>();
        List<SchoolUserDO> schoolUserDOList = schoolUserDao.selectUserInformationListByGroupId(groupId);
        if (!CollectionUtils.isEmpty(schoolUserDOList)) {
            schoolUserDOList.forEach(schoolUserDO -> adminGroupUserDTOList.add(AdminGroupUserDTO.valueOf(schoolUserDO)));
        }
        return adminGroupUserDTOList;
    }

    @Override
    public List<GroupUserDTO> userApplyList(int pageNumber) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = schoolUserApplyDao.selectIdsByUserId(currentUserDO.getUserId());
        return applyListByIds(ids);
    }

    private List<GroupUserDTO> applyListByIds(List<Long> ids) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        if (!CollectionUtils.isEmpty(ids)) {
            List<SchoolUserApplyDO> schoolUserApplyDOS = schoolUserApplyDao.selectApplyRecordByIds(ids);
            schoolUserApplyDOS.forEach(schoolUserApplyDO -> groupUserDTOList.add(GroupUserDTO.valueOf(schoolUserApplyDO)));
        }
        return groupUserDTOList;
    }

    @Override
    public List<GroupUserDTO> adminApplyList(int pageNumber) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<SchoolUserApplyDO> schoolUserApplyDOList = schoolUserApplyDao.selectReviewRecordByGroupUserId(currentUserDO.getUserId());
        if (!CollectionUtils.isEmpty(schoolUserApplyDOList)) {
            schoolUserApplyDOList.forEach(schoolUserApplyDO -> groupUserDTOList.add(GroupUserDTO.valueOf(schoolUserApplyDO)));
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
        SchoolUserApplyDO schoolUserApplyDO = schoolUserApplyDao.selectByApplyId(groupUserQuery.getApplyId());
        if (schoolUserApplyDO != null) {
            groupUserDTO = GroupUserDTO.valueOf(schoolUserApplyDO);
            groupUserDTO.setReviewStatus(ReviewStatusEnum.intStatusToEnum(schoolUserApplyDO.getStatus()).toString());
        }
        return groupUserDTO;
    }

    @Override
    public GroupUserDTO adminInformation(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        SchoolUserApplyDO schoolUserApplyDO = schoolUserApplyDao.selectGroupIdByApplyId(groupUserQuery.getApplyId());
        if (schoolUserApplyDO == null) {
            throw new InvalidParameterException("没有申请ApplyId");
        }
        if (schoolGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), schoolUserApplyDO.getSchoolGroupDO().getGroupId()) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }

        return applyInformation(groupUserQuery);
    }

    @Override
    public FileDTO userExcel(long groupId) {
        FileDTO fileDTO = null;
        List<SchoolUserDO> schoolUserDOList = schoolUserDao.selectUserListInfoExportExcel(groupId);

        if (!CollectionUtils.isEmpty(schoolUserDOList)) {
            Workbook wb = new HSSFWorkbook();
            CreationHelper createHelper = wb.getCreationHelper();
            Sheet sheet = wb.createSheet("new sheet");

            Row baseRow = sheet.createRow(0);

            baseRow.createCell(0).setCellValue("ID");
            baseRow.createCell(1).setCellValue("姓名");
            baseRow.createCell(2).setCellValue("权限");
            baseRow.createCell(3).setCellValue("性别");
            baseRow.createCell(4).setCellValue("学校名称");
            baseRow.createCell(5).setCellValue("班级名称");
            baseRow.createCell(6).setCellValue("身份");
            baseRow.createCell(7).setCellValue("手机号");
            baseRow.createCell(8).setCellValue("公司名称");
            baseRow.createCell(9).setCellValue("职位");
            baseRow.createCell(10).setCellValue("个人简介");
            baseRow.createCell(11).setCellValue("加入时间");
            baseRow.createCell(12).setCellValue("退出时间");
            baseRow.createCell(13).setCellValue("状态");

            IntStream.range(0, schoolUserDOList.size()).forEach(idx -> {
                Row row = sheet.createRow(idx + 1);

                SchoolUserDO schoolUserDO = schoolUserDOList.get(idx);
                String joinAt = LocalDateTime.ofInstant(
                        Instant.ofEpochSecond(Long.valueOf(schoolUserDO.getJoinedAt())), ZoneId.of("UTC+08:00")).
                        format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                String quiteAt = null;
                if (schoolUserDO.getQuitedAt() != 0) {
                    quiteAt = LocalDateTime.ofInstant(
                            Instant.ofEpochSecond(Long.valueOf(schoolUserDO.getQuitedAt())), ZoneId.of("UTC+08:00")).
                            format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                }


                String graduateAt = LocalDateTime.ofInstant(
                        Instant.ofEpochSecond(Long.valueOf(schoolUserDO.getGraduatedAt())), ZoneId.of("UTC+08:00")).
                        format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());

                String role = null;
                if (schoolUserDO.getSchoolGroupDO() != null) {
                    if (schoolUserDO.getSchoolGroupDO().getUserDO() != null) {
                        if (schoolUserDO.getUserDO().getUserId() == schoolUserDO.getSchoolGroupDO().getUserDO().getUserId()) {
                            role = UserRoleEnum.ADMIN.getStringStatus();
                        } else {
                            role = UserRoleEnum.MEMBER.getStringStatus();
                        }
                    }
                }
                row.createCell(0).setCellValue(schoolUserDO.getUserDO().getUserId());
                row.createCell(1).setCellValue(schoolUserDO.getName());
                row.createCell(2).setCellValue(role);
                row.createCell(3).setCellValue(schoolUserDO.getGender() == 1 ? GenderEnum.MAN.getStringStatus() : GenderEnum.WOMAN.getStringStatus());
                row.createCell(4).setCellValue(schoolUserDO.getSchoolName());
                row.createCell(5).setCellValue(schoolUserDO.getType() == 1 ? SchoolTypeEnum.STUDENT.getStringStatus() : SchoolTypeEnum.TEACHER.getStringStatus());
                row.createCell(6).setCellValue(graduateAt);
                row.createCell(7).setCellValue(schoolUserDO.getMobile());
                row.createCell(8).setCellValue(schoolUserDO.getCompany());
                row.createCell(9).setCellValue(schoolUserDO.getPosition());
                row.createCell(10).setCellValue(schoolUserDO.getBrief());
                row.createCell(11).setCellValue(joinAt);
                row.createCell(12).setCellValue(quiteAt);
                row.createCell(13).setCellValue(schoolUserDO.getStatus() == 1 ? UserStatusEnum.ENABLE.getStringStatus() : UserStatusEnum.DISABLE.getStringStatus());
            });
            LocalDate localDate = LocalDate.now();
            String basePath = customEnvironmentConfig.getUploadLocation() + customEnvironmentConfig.getExcelLocation();
            String filename = fileUtil.filename(basePath, "xls", localDate.format(DateTimeFormatEnum.COMMON_DATE.getDateTimeFormatter()) + "-" + schoolUserDOList.get(0).getSchoolGroupDO().getGroupName());
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
        SchoolUserDO schoolUserDO = schoolUserDao.userInfoAdmin(groupUserQuery.getUserId(), groupUserQuery.getGroupId());
        GroupUserDTO groupUserDTO = GroupUserDTO.valueOf(schoolUserDO);
        return groupUserDTO;
    }

    @Override
    public List<GroupUserDTO> searchUserInfo(GroupUserQuery groupUserQuery) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        //先判断用户是不是群用户
        SchoolUserDO schoolUserDO2 = schoolUserDao.selectIdxByGroupIdAndUserId(groupUserQuery.getGroupId(), userDO.getUserId());
        if (schoolUserDO2 != null) {
            List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
            SchoolUserDO schoolUserDO = SchoolUserDO.valueOf(groupUserQuery);
            List<SchoolUserDO> schoolUserDOList = schoolUserDao.searchUserInfo(schoolUserDO);
            schoolUserDOList.forEach(schoolUserDO1 -> groupUserDTOList.add(GroupUserDTO.valueOf(schoolUserDO1)));
            return groupUserDTOList;
        } else {
            throw new InvalidParameterException("当前用户不是当前群的用户");
        }
    }

    @Override
    public GroupUserDTO isMember(GroupUserQuery groupUserQuery) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        GroupUserDTO groupUserDTO = new GroupUserDTO();
        SchoolUserDO schoolUserDO = schoolUserDao.selectIdxByGroupIdAndUserId(groupUserQuery.getGroupId(), userDO.getUserId());
        if (schoolUserDO != null) {
            groupUserDTO.setMember(true);
        }
        return groupUserDTO;
    }

    @Override
    public GroupUserDTO isApply(GroupUserQuery groupUserQuery) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        GroupUserDTO groupUserDTO = new GroupUserDTO();
        SchoolUserApplyDO schoolUserApplyDO = schoolUserApplyDao.isApplyUser(userDO.getUserId(), groupUserQuery.getGroupId());
        if (schoolUserApplyDO != null) {
            groupUserDTO = GroupUserDTO.valueOf(schoolUserApplyDO);
            groupUserDTO.setHasApplied(true);
        }
        return groupUserDTO;
    }

}
