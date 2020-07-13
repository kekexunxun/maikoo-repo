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
import org.springframework.transaction.annotation.Transactional;
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
@Transactional
@Service
public class ClassGroupUserService implements GroupUserService {
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;
    @Autowired
    private ClassGroupDao classGroupDao;
    @Autowired
    private ClassUserDao classUserDao;
    @Autowired
    private ClassUserFavDao classUserFavDao;
    @Autowired
    private ClassUserApplyDao classUserApplyDao;
    @Autowired
    private UserDao userDao;
    @Autowired
    private FormIdDao formIdDao;
    @Autowired
    private FileUtil fileUtil;
    @Autowired
    private WechatUtil wechatUtil;
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

        ClassUserDO currentClassUserDO = classUserDao.selectByGroupIdAndUserId(groupId, userId);

        switch (groupUserQuery.getAction()) {
            case FILL:
                groupAdminSave(groupUserQuery, currentUserDO, currentClassUserDO);
                break;
            case UPDATE:
                groupUserSave(groupUserQuery, currentUserDO, currentClassUserDO);
                break;
            case APPLY:
                applyGroupUserSave(groupUserQuery, currentUserDO, currentClassUserDO);
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
    private void groupAdminSave(GroupUserQuery groupUserQuery, UserDO currentUserDO, ClassUserDO currentClassUserDO) {
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        if (currentClassUserDO != null) {
            log.info("群管理员已存在。group id: {}, user id: {}", groupId, userId);
            throw new InvalidParameterException();
        }

        String key = groupUserQuery.getGroupType() + "_" + currentUserDO.getUserId() + "_" + groupUserQuery.getGroupId();

        ClassGroupDO classGroupDO = (ClassGroupDO) redisUtil.value(key);
        classGroupDao.insert(classGroupDO);

        ClassUserDO classUserDO = ClassUserDO.valueOf(groupUserQuery);
        classUserDO.setClassGroupDO(classGroupDO);
        classUserDO.setUserDO(currentUserDO);
        int insert = classUserDao.insert(classUserDO);

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
    private void groupUserSave(GroupUserQuery groupUserQuery, UserDO currentUserDO, ClassUserDO currentClassUserDO) {
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        if (currentClassUserDO == null) {
            log.info("群成员不存在。group id: {}, user id: {}", groupId, userId);
            throw new InvalidParameterException();
        }

        ClassUserDO newClassUserDO = ClassUserDO.valueOf(groupUserQuery);
        newClassUserDO.setIdx(currentClassUserDO.getIdx());
        classUserDao.update(newClassUserDO);
    }

    /**
     * 信息更新（申请成员）
     * 新增申请信息
     *
     * @param groupUserQuery
     * @param currentUserDO
     */
    private void applyGroupUserSave(GroupUserQuery groupUserQuery, UserDO currentUserDO, ClassUserDO currentClassUserDO) {
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        Long notReviewApplyId = classUserApplyDao.selectNotReviewedApplyByGroupIdAndUserId(groupId, userId);

        if (currentClassUserDO != null || notReviewApplyId != null) {
            log.info("群成员已存在或者申请记录已存在且未审核。group id: {}, user id: {}", groupId, userId);
            throw new InvalidParameterException();
        }

        ClassUserApplyDO classUserApplyDO = ClassUserApplyDO.valueOf(groupUserQuery);
        classUserApplyDO.setUserDO(currentUserDO);
        classUserApplyDao.insert(classUserApplyDO);
    }

    @Override
    public void remove(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        if (classGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), groupUserQuery.getGroupId()) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }
        if (currentUserDO.getUserId() == groupUserQuery.getUserId()) {
            //TODO 群管理员自己不能删除自己
            throw new InvalidParameterException("群的管理员不能删除自己");
        }
        ClassUserDO processedRemoveClassUserDO = new ClassUserDO();
        processedRemoveClassUserDO.setUserDO(currentUserDO);

        UserDO userDO = new UserDO();
        userDO.setUserId(groupUserQuery.getUserId());

        ClassGroupDO classGroupDO = new ClassGroupDO();
        classGroupDO.setGroupId(groupUserQuery.getGroupId());

        ClassUserDO classUserDO = new ClassUserDO();
        classUserDO.setUserDO(userDO);
        classUserDO.setClassGroupDO(classGroupDO);
        classUserDO.setProcessedRemoveClassUserDO(processedRemoveClassUserDO);
        String userName = classUserDao.getUserName(groupUserQuery.getUserId(), groupUserQuery.getGroupId());
        int count = classUserDao.delete(classUserDO);
        if (count > 0) {
            //添加站内消息
            String groupName = classGroupDao.getGroupName(groupUserQuery.getGroupId());
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

        ClassUserDO classUserDO = classUserDao.selectByGroupIdAndUserId(groupUserQuery.getGroupId(), groupUserQuery.getUserId());

        if (classUserDO != null) {
            ClassUserFavDO classUserFavDO = new ClassUserFavDO();
            classUserFavDO.setUserId(currentUserDO.getUserId());
            classUserFavDO.setClassUserDO(classUserDO);
            classUserFavDao.insert(classUserFavDO);
        }
    }

    @Override
    public void removeFavor(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        ClassUserDO classUserDO = classUserDao.selectByGroupIdAndUserId(groupUserQuery.getGroupId(), groupUserQuery.getUserId());

        if (classUserDO != null) {
            ClassUserFavDO classUserFavDO = new ClassUserFavDO();
            classUserFavDO.setUserId(currentUserDO.getUserId());
            classUserFavDO.setClassUserDO(classUserDO);
            classUserFavDao.deleteByUserIdAndClassUserIdx(classUserFavDO);
        }
    }

    @Override
    public void updateReview(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        ClassUserApplyDO classUserApplyDO = classUserApplyDao.selectByApplyId(groupUserQuery.getApplyId());

        if (classGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), classUserApplyDO.getClassGroupDO().getGroupId()) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }

        if (classUserApplyDO != null && ReviewStatusEnum.intStatusToEnum(classUserApplyDO.getStatus()) == ReviewStatusEnum.PENDING) {
            ClassUserApplyDO newClassUserApplyDO = new ClassUserApplyDO();
            newClassUserApplyDO.setProcessedUserId(currentUserDO.getUserId());
            newClassUserApplyDO.setStatus(groupUserQuery.getResult().getIntStatus());
            newClassUserApplyDO.setIdx(groupUserQuery.getApplyId());
            classUserApplyDao.updateStatus(newClassUserApplyDO);

            if (groupUserQuery.getResult() == ReviewStatusEnum.APPROVE) {
                ClassUserDO classUserDO = new ClassUserDO();
                classUserDO.setUserDO(classUserApplyDO.getUserDO());
                classUserDO.setName(classUserApplyDO.getName());
                classUserDO.setGender(classUserApplyDO.getGender());
                classUserDO.setMobile(classUserApplyDO.getMobile());
                classUserDO.setCompany(classUserApplyDO.getCompany());
                classUserDO.setPosition(classUserApplyDO.getPosition());
                classUserDO.setBrief(classUserApplyDO.getBrief());
                classUserDO.setType(classUserApplyDO.getType());
                classUserDO.setClassGroupDO(classUserApplyDO.getClassGroupDO());

                classUserDao.insert(classUserDO);
            }

            Thread thread = new Thread(new Runnable() {
                @Override
                public void run() {
                    long groupId = classUserApplyDO.getClassGroupDO().getGroupId();
                    long userId = classUserApplyDO.getUserDO().getUserId();

                    List<Long> ids = classUserDao.selectIdsByGroupId(groupId);
//                    ClassUserDO processedClassUserDO = classUserDao.selectByGroupIdAndUserId(groupId, currentUserDO.getUserId());
                    UserDO applyUserDO = userDao.selectOne(userId);
                    String dateTime = LocalDateTime.
                            ofInstant(Instant.ofEpochSecond(Long.valueOf(classUserApplyDO.getAppliedAt())), ZoneId.of("UTC+08:00")).
                            format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());

                    templateMessageUtil.applyResult(GroupTypeEnum.CLASS, applyUserDO, groupUserQuery.getApplyId(), groupId, classUserApplyDO.getClassGroupDO().getGroupName(), classUserApplyDO.getBrief(), dateTime, groupUserQuery.getResult());
                }
            });
            thread.start();
        }
    }

    private GroupUserDTO applyInformation(GroupUserQuery groupUserQuery) {
        GroupUserDTO groupUserDTO = null;
        ClassUserApplyDO classUserApplyDO = classUserApplyDao.selectByApplyId(groupUserQuery.getApplyId());
        if (classUserApplyDO != null) {
            groupUserDTO = GroupUserDTO.valueOf(classUserApplyDO);
            groupUserDTO.setReviewStatus(ReviewStatusEnum.intStatusToEnum(classUserApplyDO.getStatus()).toString());
        }
        return groupUserDTO;
    }

    @Override
    public GroupUserDTO userInformation(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        if (currentUserDO.getUserId() == groupUserQuery.getUserId()) {
            throw new InvalidParameterException("用户不匹配");
        }

        return applyInformation(groupUserQuery);
    }

    @Override
    public GroupUserDTO adminInformation(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        ClassUserApplyDO classUserApplyDO = classUserApplyDao.selectGroupIdByApplyId(groupUserQuery.getApplyId());
        if (classUserApplyDO == null) {
            throw new InvalidParameterException("没有申请ApplyId");
        }

        if (classGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), classUserApplyDO.getClassGroupDO().getGroupId()) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }

        return applyInformation(groupUserQuery);
    }

    @Override
    public GroupUserDTO information(GroupUserQuery groupUserQuery) {
        GroupUserDTO groupUserDTO = null;
        ClassUserDO classUserDO = classUserDao.selectByGroupIdAndUserId(groupUserQuery.getGroupId(), groupUserQuery.getUserId());
        if (classUserDO != null) {
            groupUserDTO = GroupUserDTO.valueOf(classUserDO);

            // 查询其他用户详情，需要判断是否被收藏
            UserDO userDO = (UserDO) session.getAttribute("current_user");
            if (userDO.getUserId() != groupUserQuery.getUserId()) {
                ClassUserFavDO classUserFavDO = classUserFavDao.selectByGroupUserIdAndUserId(classUserDO.getIdx(), userDO.getUserId());
                if (classUserFavDO != null) {
                    groupUserDTO.setFav(true);
                }
            }
        }
        return groupUserDTO;
    }

    /**
     * 用户查看自己的申请记录
     *
     * @param pageNumber
     * @return
     */
    @Override
    public List<GroupUserDTO> userApplyList(int pageNumber) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = classUserApplyDao.selectIdsByUserId(currentUserDO.getUserId());
        return applyListByIds(ids);
    }

    /**
     * 管理员查看群新用户的申请记录
     *
     * @param pageNumber
     * @return
     */
    @Override
    public List<GroupUserDTO> adminApplyList(int pageNumber) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<ClassUserApplyDO> classUserApplyDOList = classUserApplyDao.selectReviewRecordByGroupUserId(currentUserDO.getUserId());
        if (!CollectionUtils.isEmpty(classUserApplyDOList)) {
            classUserApplyDOList.forEach(classUserApplyDO -> groupUserDTOList.add(GroupUserDTO.valueOf(classUserApplyDO)));
        }
        return groupUserDTOList;
    }

    @Override
    public List<GroupUserDTO> favorList(int pageNumber) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = classUserFavDao.selectIdsByUserId(currentUserDO.getUserId());
        if (!CollectionUtils.isEmpty(ids)) {
            List<ClassUserFavDO> classUserFavDOList = classUserFavDao.selectByIds(ids);
            classUserFavDOList.forEach(classUserFavDO -> groupUserDTOList.add(GroupUserDTO.valueOf(classUserFavDO)));
        }
        return groupUserDTOList;
    }

    private List<GroupUserDTO> applyListByIds(List<Long> ids) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        if (!CollectionUtils.isEmpty(ids)) {
            List<ClassUserApplyDO> classUserApplyDOS = classUserApplyDao.selectApplyRecordByIds(ids);
            classUserApplyDOS.forEach(classUserApplyDO -> groupUserDTOList.add(GroupUserDTO.valueOf(classUserApplyDO)));
        }
        return groupUserDTOList;
    }

    @Override
    public List<GroupUserDTO> selectByGroup(GroupUserQuery groupUserQuery) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        PageHelper.startPage(groupUserQuery.getPageNum() > 0 ? groupUserQuery.getPageNum() : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = classUserDao.selectIdsByGroupId(groupUserQuery.getGroupId());

        if (!CollectionUtils.isEmpty(ids)) {
            List<ClassUserDO> classUserDOList = null;
            if (groupUserQuery.isHasDetail()) {
                classUserDOList = classUserDao.selectInformationByIds(ids);
            } else {
                classUserDOList = classUserDao.selectByIds(ids);
            }
            classUserDOList.forEach(classUserDO -> groupUserDTOList.add(GroupUserDTO.valueOf(classUserDO)));
        }
        return groupUserDTOList;
    }

    @Override
    public List<AdminGroupUserDTO> getUserListByGroupId(long groupId) {
        List<AdminGroupUserDTO> adminGroupUserDTOList = new ArrayList<>();
        List<ClassUserDO> classUserDOList = classUserDao.selectUserInformationListByGroupId(groupId);
        System.out.println(classUserDOList);
        if (!CollectionUtils.isEmpty(classUserDOList)) {
            classUserDOList.forEach(classUserDO -> adminGroupUserDTOList.add(AdminGroupUserDTO.valueOf(classUserDO)));
        }
        return adminGroupUserDTOList;
    }

    @Override
    public FileDTO userExcel(long groupId) {
        FileDTO fileDTO = null;
        List<ClassUserDO> classUserDOList = classUserDao.selectUserListInfoExportExcel(groupId);

        if (!CollectionUtils.isEmpty(classUserDOList)) {
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

            IntStream.range(0, classUserDOList.size()).forEach(idx -> {
                Row row = sheet.createRow(idx + 1);

                ClassUserDO classUserDO = classUserDOList.get(idx);
                String joinAt = LocalDateTime.ofInstant(
                        Instant.ofEpochSecond(Long.valueOf(classUserDO.getJoinedAt())), ZoneId.of("UTC+08:00")).
                        format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                String quiteAt = "";
                if (classUserDO.getQuitedAt() != 0) {
                    quiteAt = LocalDateTime.ofInstant(
                            Instant.ofEpochSecond(Long.valueOf(classUserDO.getQuitedAt())), ZoneId.of("UTC+08:00")).
                            format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                }

                String role = null;
                if (classUserDO.getClassGroupDO() != null) {
                    if (classUserDO.getClassGroupDO().getUserDO() != null) {
                        if (classUserDO.getUserDO().getUserId() == classUserDO.getClassGroupDO().getUserDO().getUserId()) {
                            role = UserRoleEnum.ADMIN.getStringStatus();
                        } else {
                            role = UserRoleEnum.MEMBER.getStringStatus();
                        }
                    }
                }
                row.createCell(0).setCellValue(classUserDO.getUserDO().getUserId());
                row.createCell(1).setCellValue(classUserDO.getName());
                row.createCell(2).setCellValue(role);
                row.createCell(3).setCellValue(classUserDO.getGender() == 1 ? GenderEnum.MAN.getStringStatus() : GenderEnum.WOMAN.getStringStatus());
                row.createCell(4).setCellValue(classUserDO.getSchoolName());
                row.createCell(5).setCellValue(classUserDO.getClassName());
                row.createCell(6).setCellValue(classUserDO.getType() == 1 ? SchoolTypeEnum.STUDENT.getStringStatus() : SchoolTypeEnum.TEACHER.getStringStatus());
                row.createCell(7).setCellValue(classUserDO.getMobile());
                row.createCell(8).setCellValue(classUserDO.getCompany());
                row.createCell(9).setCellValue(classUserDO.getPosition());
                row.createCell(10).setCellValue(classUserDO.getBrief());
                row.createCell(11).setCellValue(joinAt);
                row.createCell(12).setCellValue(quiteAt);
                row.createCell(13).setCellValue(classUserDO.getStatus() == 1 ? UserStatusEnum.ENABLE.getStringStatus() : UserStatusEnum.DISABLE.getStringStatus());
            });
            LocalDate localDate = LocalDate.now();
            String basePath = customEnvironmentConfig.getUploadLocation() + customEnvironmentConfig.getExcelLocation();
            String filename = fileUtil.filename(basePath, "xls", localDate.format(DateTimeFormatEnum.COMMON_DATE.getDateTimeFormatter()) + "-" + classUserDOList.get(0).getClassGroupDO().getGroupName());
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
        ClassUserDO classUserDO = classUserDao.userInfoAdmin(groupUserQuery.getUserId(), groupUserQuery.getGroupId());
        GroupUserDTO groupUserDTO = GroupUserDTO.valueOf(classUserDO);
        return groupUserDTO;
    }

    @Override
    public List<GroupUserDTO> searchUserInfo(GroupUserQuery groupUserQuery) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");

        //先判断用户是不是群用户
        ClassUserDO classUserDO2 = classUserDao.selectIdxByGroupIdAndUserId(groupUserQuery.getGroupId(), userDO.getUserId());
        if (classUserDO2 != null) {
            List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
            ClassUserDO classUserDO = ClassUserDO.valueOf(groupUserQuery);
            List<ClassUserDO> classUserDOList = classUserDao.searchUserInfo(classUserDO);
            classUserDOList.forEach(classUserDO1 -> groupUserDTOList.add(GroupUserDTO.valueOf(classUserDO1)));
            return groupUserDTOList;
        } else {
            throw new InvalidParameterException("当前用户不是当前群的用户");
        }
    }

    @Override
    public GroupUserDTO isMember(GroupUserQuery groupUserQuery) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        GroupUserDTO groupUserDTO = new GroupUserDTO();
        ClassUserDO classUserDO = classUserDao.selectIdxByGroupIdAndUserId(groupUserQuery.getGroupId(), userDO.getUserId());
        if (classUserDO != null) {
            groupUserDTO.setMember(true);
        }
        return groupUserDTO;
    }

    @Override
    public GroupUserDTO isApply(GroupUserQuery groupUserQuery) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        GroupUserDTO groupUserDTO = new GroupUserDTO();
        ClassUserApplyDO classUserApplyDO = classUserApplyDao.isApplyUser(userDO.getUserId(), groupUserQuery.getGroupId());
        if (classUserApplyDO != null) {
            groupUserDTO = GroupUserDTO.valueOf(classUserApplyDO);
            groupUserDTO.setHasApplied(true);
        }
        return groupUserDTO;
    }
}
