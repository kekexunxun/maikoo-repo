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
public class CommunityGroupUserService implements GroupUserService {
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;
    @Autowired
    private CommunityGroupDao communityGroupDao;

    @Autowired
    private CommunityUserFavDao communityUserFavDao;

    @Autowired
    private CommunityUserApplyDao communityUserApplyDao;

    @Autowired
    private CommunityUserDao communityUserDao;

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
    private ObjectMapper objectMapper;

    @Autowired
    private MessageDao messageDao;

    @Override
    public void save(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        CommunityUserDO currentCommunityUserDO = communityUserDao.selectByGroupIdAndUserId(groupId, userId);

        switch (groupUserQuery.getAction()) {
            case FILL:
                groupAdminSave(groupUserQuery, currentUserDO, currentCommunityUserDO);
                break;
            case UPDATE:
                groupUserSave(groupUserQuery, currentUserDO, currentCommunityUserDO);
                break;
            case APPLY:
                applyGroupUserSave(groupUserQuery, currentUserDO, currentCommunityUserDO);
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
    private void groupAdminSave(GroupUserQuery groupUserQuery, UserDO currentUserDO, CommunityUserDO currentCommunityUserDO) {
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        if (currentCommunityUserDO != null) {
            log.info("群管理员已存在。group id: {}, user id: {}", groupId, userId);
            throw new InvalidParameterException();
        }

        String key = groupUserQuery.getGroupType() + "_" + currentUserDO.getUserId() + "_" + groupUserQuery.getGroupId();

        CommunityGroupDO communityGroupDO = (CommunityGroupDO) redisUtil.value(key);
        communityGroupDao.insert(communityGroupDO);

        CommunityUserDO communityUserDO = CommunityUserDO.valueOf(groupUserQuery);
        communityUserDO.setCommunityGroupDO(communityGroupDO);
        communityUserDO.setUserDO(currentUserDO);
        int insert = communityUserDao.insert(communityUserDO);

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
    private void groupUserSave(GroupUserQuery groupUserQuery, UserDO currentUserDO, CommunityUserDO currentCommunityUserDO) {
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        if (currentCommunityUserDO == null) {
            log.info("群成员不存在。group id: {}, user id: {}", groupId, userId);
            throw new InvalidParameterException();
        }

        CommunityUserDO newCommunityUserDO = CommunityUserDO.valueOf(groupUserQuery);
        newCommunityUserDO.setIdx(currentCommunityUserDO.getIdx());
        communityUserDao.update(newCommunityUserDO);
    }

    /**
     * 信息更新（申请成员）
     * 新增申请信息
     *
     * @param groupUserQuery
     * @param currentUserDO
     */
    private void applyGroupUserSave(GroupUserQuery groupUserQuery, UserDO currentUserDO, CommunityUserDO currentCommunityUserDO) {
        long userId = currentUserDO.getUserId();
        long groupId = groupUserQuery.getGroupId();

        Long notReviewApplyId = communityUserApplyDao.selectNotReviewedApplyByGroupIdAndUserId(groupId, userId);

        if (currentCommunityUserDO != null || notReviewApplyId != null) {
            log.info("群成员已存在或者申请记录已存在且未审核。group id: {}, user id: {}", groupId, userId);
            throw new InvalidParameterException();
        }

        CommunityUserApplyDO communityUserApplyDO = CommunityUserApplyDO.valueOf(groupUserQuery);
        communityUserApplyDO.setUserDO(currentUserDO);
        communityUserApplyDao.insert(communityUserApplyDO);
    }

    @Override
    public void remove(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        if (communityGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), groupUserQuery.getGroupId()) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }
        if (currentUserDO.getUserId() == groupUserQuery.getUserId()) {
            //TODO 群管理员自己不能删除自己
            throw new InvalidParameterException("群的管理员不能删除自己");
        }
        CommunityUserDO processedRemoveCommunityUserDO = new CommunityUserDO();
        processedRemoveCommunityUserDO.setUserDO(currentUserDO);

        UserDO userDO = new UserDO();
        userDO.setUserId(groupUserQuery.getUserId());

        CommunityGroupDO communityGroupDO = new CommunityGroupDO();
        communityGroupDO.setGroupId(groupUserQuery.getGroupId());

        CommunityUserDO communityUserDO = new CommunityUserDO();
        communityUserDO.setUserDO(userDO);
        communityUserDO.setCommunityGroupDO(communityGroupDO);
        communityUserDO.setProcessedRemoveCommunityUserDO(processedRemoveCommunityUserDO);
        String userName = communityUserDao.getUserName(groupUserQuery.getUserId(), groupUserQuery.getGroupId());

        int count = communityUserDao.delete(communityUserDO);
        if (count > 0) {
            //添加站内消息
            String groupName = communityGroupDao.getGroupName(groupUserQuery.getGroupId());
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

        CommunityUserDO communityUserDO = communityUserDao.selectByGroupIdAndUserId(groupUserQuery.getGroupId(), groupUserQuery.getUserId());

        if (communityUserDO != null) {
            CommunityUserFavDO communityUserFavDO = new CommunityUserFavDO();
            communityUserFavDO.setUserId(currentUserDO.getUserId());
            communityUserFavDO.setCommunityUserDO(communityUserDO);
            communityUserFavDao.insert(communityUserFavDO);
        }
    }

    @Override
    public void removeFavor(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        CommunityUserDO communityUserDO = communityUserDao.selectByGroupIdAndUserId(groupUserQuery.getGroupId(), groupUserQuery.getUserId());

        if (communityUserDO != null) {
            CommunityUserFavDO communityUserFavDO = new CommunityUserFavDO();
            communityUserFavDO.setUserId(currentUserDO.getUserId());
            communityUserFavDO.setCommunityUserDO(communityUserDO);
            communityUserFavDao.deleteByUserIdAndClassUserIdx(communityUserFavDO);
        }
    }

    @Override
    public void updateReview(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        CommunityUserApplyDO communityUserApplyDO = communityUserApplyDao.selectByApplyId(groupUserQuery.getApplyId());

        if (communityGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), communityUserApplyDO.getCommunityGroupDO().getGroupId()) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }

        if (communityUserApplyDO != null && ReviewStatusEnum.intStatusToEnum(communityUserApplyDO.getStatus()) == ReviewStatusEnum.PENDING) {
            CommunityUserApplyDO newCommunityUserApplyDO = new CommunityUserApplyDO();
            newCommunityUserApplyDO.setProcessedUserId(currentUserDO.getUserId());
            newCommunityUserApplyDO.setStatus(groupUserQuery.getResult().getIntStatus());
            newCommunityUserApplyDO.setIdx(groupUserQuery.getApplyId());
            communityUserApplyDao.updateStatus(newCommunityUserApplyDO);

            if (groupUserQuery.getResult() == ReviewStatusEnum.APPROVE) {
                CommunityUserDO communityUserDO = new CommunityUserDO();
                communityUserDO.setUserDO(communityUserApplyDO.getUserDO());
                communityUserDO.setName(communityUserApplyDO.getName());
                communityUserDO.setGender(communityUserApplyDO.getGender());
                communityUserDO.setMobile(communityUserApplyDO.getMobile());
                communityUserDO.setCompany(communityUserApplyDO.getCompany());
                communityUserDO.setPosition(communityUserApplyDO.getPosition());
                communityUserDO.setBrief(communityUserApplyDO.getBrief());
                communityUserDO.setType(communityUserApplyDO.getType());
                communityUserDO.setBuilding(communityUserApplyDO.getBuilding());
                communityUserDO.setRoom(communityUserApplyDO.getRoom());
                communityUserDO.setCommunityGroupDO(communityUserApplyDO.getCommunityGroupDO());

                communityUserDao.insert(communityUserDO);
            }

            Thread thread = new Thread(new Runnable() {
                @Override
                public void run() {
                    long groupId = communityUserApplyDO.getCommunityGroupDO().getGroupId();
                    long userId = communityUserApplyDO.getUserDO().getUserId();

                    List<Long> ids = communityUserDao.selectIdsByGroupId(groupId);
//                    CommunityUserDO processedCommunityUserDO = communityUserDao.selectByGroupIdAndUserId(groupId, currentUserDO.getUserId());
                    UserDO applyUserDO = userDao.selectOne(userId);
                    String dateTime = LocalDateTime.
                            ofInstant(Instant.ofEpochSecond(Long.valueOf(communityUserApplyDO.getAppliedAt())), ZoneId.of("UTC+08:00")).
                            format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());

                    templateMessageUtil.applyResult(GroupTypeEnum.COMMUNITY, applyUserDO, groupUserQuery.getApplyId(), groupId, communityUserApplyDO.getCommunityGroupDO().getGroupName(), communityUserApplyDO.getBrief(), dateTime, groupUserQuery.getResult());
                }
            });
            thread.start();
        }
    }

    @Override
    public GroupUserDTO information(GroupUserQuery groupUserQuery) {
        GroupUserDTO groupUserDTO = null;
        CommunityUserDO communityUserDO = communityUserDao.selectByGroupIdAndUserId(groupUserQuery.getGroupId(), groupUserQuery.getUserId());
        if (communityUserDO != null) {
            groupUserDTO = GroupUserDTO.valueOf(communityUserDO);

            UserDO userDO = (UserDO) session.getAttribute("current_user");
            if (groupUserQuery.getUserId() != userDO.getUserId()) {
                CommunityUserFavDO communityUserFavDO = communityUserFavDao.selectByGroupUserIdAndUserId(communityUserDO.getIdx(), userDO.getUserId());
                if (communityUserFavDO != null) {
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
        List<Long> ids = communityUserFavDao.selectIdsByUserId(currentUserDO.getUserId());
        if (!CollectionUtils.isEmpty(ids)) {
            List<CommunityUserFavDO> communityUserFavDOList = communityUserFavDao.selectByIds(ids);
            communityUserFavDOList.forEach(communityUserFavDO -> groupUserDTOList.add(GroupUserDTO.valueOf(communityUserFavDO)));
        }
        return groupUserDTOList;
    }

    @Override
    public List<GroupUserDTO> selectByGroup(GroupUserQuery groupUserQuery) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        PageHelper.startPage(groupUserQuery.getPageNum() > 0 ? groupUserQuery.getPageNum() : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = communityUserDao.selectIdsByGroupId(groupUserQuery.getGroupId());
        if (!CollectionUtils.isEmpty(ids)) {
            List<CommunityUserDO> communityUserDOList = null;
            if (groupUserQuery.isHasDetail()) {
                communityUserDOList = communityUserDao.selectInformationByIds(ids);
            } else {
                communityUserDOList = communityUserDao.selectByIds(ids);
            }
            communityUserDOList.forEach(communityUserDO -> groupUserDTOList.add(GroupUserDTO.valueOf(communityUserDO)));
        }

        return groupUserDTOList;
    }

    @Override
    public List<AdminGroupUserDTO> getUserListByGroupId(long groupId) {
        List<AdminGroupUserDTO> adminGroupUserDTOList = new ArrayList<>();
        List<CommunityUserDO> communityUserDOList = communityUserDao.selectUserInformationListByGroupId(groupId);
        if (!CollectionUtils.isEmpty(communityUserDOList)) {
            communityUserDOList.forEach(communityUserDO -> adminGroupUserDTOList.add(AdminGroupUserDTO.valueOf(communityUserDO)));
        }
        return adminGroupUserDTOList;
    }

    /**
     * 我的群申请
     *
     * @param pageNumber
     * @return
     */
    @Override
    public List<GroupUserDTO> userApplyList(int pageNumber) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = communityUserApplyDao.selectIdsByUserId(currentUserDO.getUserId());
        return applyListByIds(ids);
    }

    private List<GroupUserDTO> applyListByIds(List<Long> ids) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        if (!CollectionUtils.isEmpty(ids)) {
            List<CommunityUserApplyDO> communityUserApplyDOS = communityUserApplyDao.selectApplyRecordByIds(ids);
            communityUserApplyDOS.forEach(communityUserApplyDO -> groupUserDTOList.add(GroupUserDTO.valueOf(communityUserApplyDO)));
        }
        return groupUserDTOList;
    }

    @Override
    public List<GroupUserDTO> adminApplyList(int pageNumber) {
        List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<CommunityUserApplyDO> communityUserApplyDOList = communityUserApplyDao.selectReviewRecordByGroupUserId(currentUserDO.getUserId());
        if (!CollectionUtils.isEmpty(communityUserApplyDOList)) {
            communityUserApplyDOList.forEach(communityUserApplyDO -> groupUserDTOList.add(GroupUserDTO.valueOf(communityUserApplyDO)));
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
        CommunityUserApplyDO communityUserApplyDO = communityUserApplyDao.selectByApplyId(groupUserQuery.getApplyId());
        if (communityUserApplyDO != null) {
            groupUserDTO = GroupUserDTO.valueOf(communityUserApplyDO);
            groupUserDTO.setReviewStatus(ReviewStatusEnum.intStatusToEnum(communityUserApplyDO.getStatus()).toString());
        }
        return groupUserDTO;
    }

    @Override
    public GroupUserDTO adminInformation(GroupUserQuery groupUserQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        CommunityUserApplyDO communityUserApplyDO = communityUserApplyDao.selectGroupIdByApplyId(groupUserQuery.getApplyId());
        if (communityUserApplyDO == null) {
            throw new InvalidParameterException("没有申请ApplyId");
        }

        if (communityGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), communityUserApplyDO.getCommunityGroupDO().getGroupId()) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }

        return applyInformation(groupUserQuery);
    }

    @Override
    public FileDTO userExcel(long groupId) {
        FileDTO fileDTO = null;
        List<CommunityUserDO> communityUserDOList = communityUserDao.selectUserListInfoExportExcel(groupId);

        if (!CollectionUtils.isEmpty(communityUserDOList)) {
            Workbook wb = new HSSFWorkbook();
            CreationHelper createHelper = wb.getCreationHelper();
            Sheet sheet = wb.createSheet("new sheet");

            Row baseRow = sheet.createRow(0);

            baseRow.createCell(0).setCellValue("ID");
            baseRow.createCell(1).setCellValue("姓名");
            baseRow.createCell(2).setCellValue("权限");
            baseRow.createCell(3).setCellValue("性别");
            baseRow.createCell(4).setCellValue("身份");
            baseRow.createCell(5).setCellValue("楼号");
            baseRow.createCell(6).setCellValue("房号");
            baseRow.createCell(7).setCellValue("手机号");
            baseRow.createCell(8).setCellValue("公司名称");
            baseRow.createCell(9).setCellValue("职位");
            baseRow.createCell(10).setCellValue("个人简介");
            baseRow.createCell(11).setCellValue("加入时间");
            baseRow.createCell(12).setCellValue("退出时间");
            baseRow.createCell(13).setCellValue("状态");

            IntStream.range(0, communityUserDOList.size()).forEach(idx -> {
                Row row = sheet.createRow(idx + 1);

                CommunityUserDO communityUserDO = communityUserDOList.get(idx);
                String joinAt = LocalDateTime.ofInstant(
                        Instant.ofEpochSecond(Long.valueOf(communityUserDO.getJoinedAt())), ZoneId.of("UTC+08:00")).
                        format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                String quiteAt = "";
                if (communityUserDO.getQuitedAt() != 0) {
                    quiteAt = LocalDateTime.ofInstant(
                            Instant.ofEpochSecond(Long.valueOf(communityUserDO.getQuitedAt())), ZoneId.of("UTC+08:00")).
                            format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                }

                String role = null;
                if (communityUserDO.getCommunityGroupDO() != null) {
                    if (communityUserDO.getCommunityGroupDO().getUserDO() != null) {
                        if (communityUserDO.getUserDO().getUserId() == communityUserDO.getCommunityGroupDO().getUserDO().getUserId()) {
                            role = UserRoleEnum.ADMIN.getStringStatus();
                        } else {
                            role = UserRoleEnum.MEMBER.getStringStatus();
                        }
                    }
                }
                row.createCell(0).setCellValue(communityUserDO.getUserDO().getUserId());
                row.createCell(1).setCellValue(communityUserDO.getName());
                row.createCell(2).setCellValue(role);
                row.createCell(3).setCellValue(communityUserDO.getGender() == 1 ? GenderEnum.MAN.getStringStatus() : GenderEnum.WOMAN.getStringStatus());
                row.createCell(4).setCellValue(communityUserDO.getType() == 1 ? CommunityTypeEnum.OWNER.getStringStatus() : CommunityTypeEnum.MANAGER.getStringStatus());
                row.createCell(5).setCellValue(communityUserDO.getBuilding());
                row.createCell(6).setCellValue(communityUserDO.getRoom());
                row.createCell(7).setCellValue(communityUserDO.getMobile());
                row.createCell(8).setCellValue(communityUserDO.getCompany());
                row.createCell(9).setCellValue(communityUserDO.getPosition());
                row.createCell(10).setCellValue(communityUserDO.getBrief());
                row.createCell(11).setCellValue(joinAt);
                row.createCell(12).setCellValue(quiteAt);
                row.createCell(13).setCellValue(communityUserDO.getStatus() == 1 ? UserStatusEnum.ENABLE.getStringStatus() : UserStatusEnum.DISABLE.getStringStatus());
            });
            LocalDate localDate = LocalDate.now();
            String basePath = customEnvironmentConfig.getUploadLocation() + customEnvironmentConfig.getExcelLocation();
            String filename = fileUtil.filename(basePath, "xls", localDate.format(DateTimeFormatEnum.COMMON_DATE.getDateTimeFormatter()) + "-" + communityUserDOList.get(0).getCommunityGroupDO().getGroupName());
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
        CommunityUserDO communityUserDO = communityUserDao.userInfoAdmin(groupUserQuery.getUserId(), groupUserQuery.getGroupId());
        GroupUserDTO groupUserDTO = GroupUserDTO.valueOf(communityUserDO);
        return groupUserDTO;
    }

    @Override
    public List<GroupUserDTO> searchUserInfo(GroupUserQuery groupUserQuery) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");

        //先判断用户是不是群用户
        CommunityUserDO communityUserDO2 = communityUserDao.selectIdxByGroupIdAndUserId(groupUserQuery.getGroupId(), userDO.getUserId());
        if (communityUserDO2 != null) {
            List<GroupUserDTO> groupUserDTOList = new ArrayList<>();
            CommunityUserDO communityUserDO = CommunityUserDO.valueOf(groupUserQuery);
            List<CommunityUserDO> communityUserDOList = communityUserDao.searchUserInfo(communityUserDO);
            communityUserDOList.forEach(communityUserDO1 -> groupUserDTOList.add(GroupUserDTO.valueOf(communityUserDO1)));
            return groupUserDTOList;
        } else {
            throw new InvalidParameterException("当前用户不是当前群的用户");
        }
    }

    @Override
    public GroupUserDTO isMember(GroupUserQuery groupUserQuery) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        GroupUserDTO groupUserDTO = new GroupUserDTO();
        CommunityUserDO communityUserDO = communityUserDao.selectIdxByGroupIdAndUserId(groupUserQuery.getGroupId(), userDO.getUserId());
        if (communityUserDO != null) {
            groupUserDTO.setMember(true);
        }
        return groupUserDTO;
    }

    @Override
    public GroupUserDTO isApply(GroupUserQuery groupUserQuery) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        GroupUserDTO groupUserDTO = new GroupUserDTO();
        CommunityUserApplyDO communityUserApplyDO = communityUserApplyDao.isApplyUser(userDO.getUserId(), groupUserQuery.getGroupId());
        if (communityUserApplyDO != null) {
            groupUserDTO = GroupUserDTO.valueOf(communityUserApplyDO);
            groupUserDTO.setHasApplied(true);
        }
        return groupUserDTO;
    }


}
