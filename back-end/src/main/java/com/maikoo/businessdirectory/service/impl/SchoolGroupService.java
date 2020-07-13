package com.maikoo.businessdirectory.service.impl;

import com.google.common.collect.Lists;
import com.maikoo.businessdirectory.config.CustomEnvironmentConfig;
import com.maikoo.businessdirectory.dao.MessageDao;
import com.maikoo.businessdirectory.dao.SchoolGroupDao;
import com.maikoo.businessdirectory.dao.SchoolUserDao;
import com.maikoo.businessdirectory.dao.UserDao;
import com.maikoo.businessdirectory.model.*;
import com.maikoo.businessdirectory.model.constant.DateTimeFormatEnum;
import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.model.constant.UserRoleEnum;
import com.maikoo.businessdirectory.model.dto.FileDTO;
import com.maikoo.businessdirectory.model.dto.GroupDTO;
import com.maikoo.businessdirectory.model.dto.PostDTO;
import com.maikoo.businessdirectory.model.query.GroupInformationQuery;
import com.maikoo.businessdirectory.model.query.GroupQuery;
import com.maikoo.businessdirectory.model.query.PosterQuery;
import com.maikoo.businessdirectory.model.query.SchoolGroupInformationQuery;
import com.maikoo.businessdirectory.service.AbstractGroupService;
import com.maikoo.businessdirectory.service.AddressService;
import com.maikoo.businessdirectory.util.*;
import org.apache.poi.hssf.usermodel.HSSFWorkbook;
import org.apache.poi.ss.usermodel.CreationHelper;
import org.apache.poi.ss.usermodel.Row;
import org.apache.poi.ss.usermodel.Sheet;
import org.apache.poi.ss.usermodel.Workbook;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.data.redis.core.RedisTemplate;
import org.springframework.stereotype.Service;
import org.springframework.util.CollectionUtils;
import org.springframework.util.StringUtils;

import javax.annotation.Resource;
import javax.servlet.http.HttpSession;
import java.awt.image.BufferedImage;
import java.io.FileOutputStream;
import java.io.OutputStream;
import java.security.InvalidParameterException;
import java.time.Instant;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.ZoneId;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.TimeUnit;
import java.util.stream.IntStream;

@Service
public class SchoolGroupService extends AbstractGroupService {
    @Autowired
    private AddressService addressService;

    @Autowired
    private SchoolGroupDao schoolGroupDao;

    @Autowired
    private SchoolUserDao schoolUserDao;

    @Autowired
    private UserDao userDao;

    @Autowired
    private MessageDao messageDao;

    @Autowired
    private WechatUtil wechatUtil;

    @Autowired
    private FileUtil fileUtil;

    @Autowired
    private RedisUtil redisUtil;

    @Autowired
    private PosterUtil posterUtil;

    @Autowired
    private TemplateMessageUtil templateMessageUtil;

    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;

    @Autowired
    private HttpSession session;

    @Resource
    private RedisTemplate<String, Object> redisTemplate;


    @Override
    public GroupDTO insert(GroupInformationQuery groupInformationQuery) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        SchoolGroupInformationQuery schoolGroupInformationQuery = asSchoolGroupInformationQuery(groupInformationQuery);
        SchoolGroupDO schoolGroupDO = SchoolGroupDO.valueOf(schoolGroupInformationQuery);
        schoolGroupDO.setUserDO(currentUserDO);

        String key = redisUtil.groupSN(GroupTypeEnum.SCHOOL, currentUserDO.getUserId());
        redisTemplate.opsForValue().set(key, schoolGroupDO, 10 * 60, TimeUnit.SECONDS);

        GroupDTO groupDTO = new GroupDTO();
        groupDTO.setGroupId(Long.valueOf(key.split("_")[2]));
        return groupDTO;
    }

    @Override
    public void update(GroupInformationQuery groupInformationQuery) {
        //判断是不是管理员
        groupAdmin(groupInformationQuery.getGroupId());
        SchoolGroupInformationQuery schoolGroupInformationQuery = asSchoolGroupInformationQuery(groupInformationQuery);
        SchoolGroupDO schoolGroupDO = SchoolGroupDO.valueOf(schoolGroupInformationQuery);
        schoolGroupDao.update(schoolGroupDO);

    }

    @Override
    public void remove(long id) {
        groupAdmin(id);
        int dismissResult = schoolGroupDao.dissmiss(id);
        String groupName = schoolGroupDao.getGroupName(id);
        List<Long> ids = schoolUserDao.selectUserIdByGroupId(id);
        List<MessageDO> messageDOList = new ArrayList<>();
        for (Long userId : ids) {
            MessageDO messageDO = new MessageDO();
            messageDO.setMsgTitle("系统通知");
            messageDO.setMsgContent("「" + groupName + "」群已被管理员解散");
            messageDO.setSendTo(userId);
            messageDOList.add(messageDO);
        }
        messageDao.createMessage(messageDOList);

        Thread thread = new Thread(new Runnable() {
            @Override
            public void run() {
                String dateTime = LocalDateTime.now().format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());

                ids.forEach(userId -> {
                    UserDO userDO = userDao.selectOne(userId);
                    templateMessageUtil.dismiss(userDO.getOpenid(), groupName, dateTime);
                });
            }
        });

        if(dismissResult == 1){
            thread.start();
        }
    }

    @Override
    public void changeOwner(GroupQuery groupQuery) {
        groupAdmin(groupQuery.getGroupId());
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        int count = schoolGroupDao.changeOwner(groupQuery.getUserId(), groupQuery.getGroupId());
        if (count > 0) {
            //发送站内消息
            String groupName = schoolGroupDao.getGroupName(groupQuery.getGroupId());
            String userName = schoolUserDao.getUserName(currentUserDO.getUserId(), groupQuery.getGroupId());
            List<MessageDO> messageDOList = new ArrayList<>();
            MessageDO messageDO = new MessageDO();
            messageDO.setMsgTitle("系统通知");
            messageDO.setMsgContent("「" + userName + "」已将「" + groupName + "」群的管理员权限转给您");
            messageDO.setSendTo(groupQuery.getUserId());
            messageDOList.add(messageDO);
            //发站内消息给自己
            String acceptName = schoolUserDao.getUserName(groupQuery.getUserId(), groupQuery.getGroupId());
            MessageDO messageDO2 = new MessageDO();
            messageDO2.setMsgTitle("系统通知");
            messageDO2.setMsgContent("您已将群「" + groupName + "」的管理员权限转移给「" + acceptName + "」，您将不再拥有群「" + groupName + "」的管理权限");
            messageDO2.setSendTo(currentUserDO.getUserId());
            messageDOList.add(messageDO2);

            messageDao.createMessage(messageDOList);

            Thread thread = new Thread(new Runnable() {
                @Override
                public void run() {
                    long userId = groupQuery.getUserId();
                    long groupId = groupQuery.getGroupId();
                    UserDO userDO = userDao.selectOne(userId);
                    SchoolGroupDO schoolGroupDO = schoolGroupDao.selectOne(groupId);
                    SchoolUserDO schoolUserDO = schoolUserDao.selectByGroupIdAndUserId(groupId, userId);

                    templateMessageUtil.changeOwner(userDO, schoolGroupDO.getGroupName(), schoolUserDO.getName());
                }
            });
            thread.start();
        }
    }

    @Override
    public PostDTO sharePost(long id) {
        SchoolGroupDO schoolGroupDO = schoolGroupDao.selectOne(id);

        if (StringUtils.isEmpty(schoolGroupDO.getPosterUrl()) || StringUtils.isEmpty(schoolGroupDO.getQrCodeUrl())) {

            try {
                String baseLocation = customEnvironmentConfig.getUploadLocation();
                String imageLocation = customEnvironmentConfig.getImageLocation();
                BufferedImage qrCodeBufferedImage = wechatUtil.qrCode(GroupTypeEnum.SCHOOL, id);

                schoolGroupDO.setQrCodeUrl("/" + fileUtil.saveImage(qrCodeBufferedImage, "PNG"));
                schoolGroupDO.setPosterUrl("/" + imageLocation + fileUtil.filename(baseLocation + imageLocation, "PNG"));

                PosterQuery posterQuery = PosterQuery.valueOf(schoolGroupDO);
                posterQuery.setQrCodeBufferedImage(qrCodeBufferedImage);

                posterUtil.commonPoster(posterQuery);

                schoolGroupDao.updateShareUrl(schoolGroupDO);
            } catch (Exception e) {
                throw new RuntimeException(e);
            }
        }

        PostDTO postDTO = new PostDTO();
        postDTO.setPosterUrl(schoolGroupDO.getPosterUrl());
        postDTO.setQrcodeUrl(schoolGroupDO.getQrCodeUrl());
        return postDTO;
    }

    @Override
    public GroupDTO information(long id) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        GroupDTO groupDTO = null;
        SchoolGroupDO schoolGroupDO = schoolGroupDao.selectOne(id);
        if (schoolGroupDO != null) {
            List<Long> ids = schoolUserDao.selectIdsByGroupId(schoolGroupDO.getGroupId());
            schoolGroupDO.setGroupMemCount(CollectionUtils.isEmpty(ids) ? 0 : ids.size());
            groupDTO = GroupDTO.valueOf(schoolGroupDO);
            if (schoolGroupDO.getUserDO().getUserId() == currentUserDO.getUserId()) {
                groupDTO.setMemType(UserRoleEnum.ADMIN);
            } else if (schoolUserDao.selectIdxByGroupIdAndUserId(schoolGroupDO.getGroupId(), currentUserDO.getUserId()) != null) {
                groupDTO.setMemType(UserRoleEnum.MEMBER);
            } else {
                groupDTO.setMemType(UserRoleEnum.STRANGER);
            }
        }
        return groupDTO;
    }

    @Override
    public GroupDTO informationByAdmin(long id) {
        GroupDTO groupDTO = null;
        SchoolGroupDO schoolGroupDO = schoolGroupDao.selectOne(id);
        if (schoolGroupDO != null) {
            groupDTO = GroupDTO.valueOf(schoolGroupDO);
            groupDTO.setGroupAddress(addressService.address(Lists.newArrayList(schoolGroupDO.getGroupAddrCode().split("_"))) + schoolGroupDO.getGroupAddrDetail());
        }
        return groupDTO;
    }

    @Override
    public List<GroupDTO> selectAllByAdmin() {
        List<GroupDTO> groupDTOList = new ArrayList<>();

        List<SchoolGroupDO> schoolGroupDOList = schoolGroupDao.selectAll();

        if (!CollectionUtils.isEmpty(schoolGroupDOList)) {
            schoolGroupDOList.forEach(schoolGroupDO -> {
                GroupDTO groupDTO = GroupDTO.valueOf(schoolGroupDO);
                groupDTO.setGroupAddress(addressService.address(Lists.newArrayList(schoolGroupDO.getGroupAddrCode().split("_"))) + schoolGroupDO.getGroupAddrDetail());
                groupDTOList.add(groupDTO);
            });
        }

        return groupDTOList;
    }

    @Override
    public FileDTO excel() {
        FileDTO fileDTO = null;
        List<SchoolGroupDO> schoolGroupDOList = schoolGroupDao.selectAll();

        if (!CollectionUtils.isEmpty(schoolGroupDOList)) {
            Workbook wb = new HSSFWorkbook();
            CreationHelper createHelper = wb.getCreationHelper();
            Sheet sheet = wb.createSheet("new sheet");

            Row baseRow = sheet.createRow(0);

            baseRow.createCell(0).setCellValue("群ID");
            baseRow.createCell(1).setCellValue("群名称");
            baseRow.createCell(2).setCellValue("群地址");
            baseRow.createCell(3).setCellValue("群简介");
            baseRow.createCell(4).setCellValue("群成员人数");
            baseRow.createCell(5).setCellValue("群创建时间");
            baseRow.createCell(6).setCellValue("群解散时间");
            baseRow.createCell(7).setCellValue("群状态");
            baseRow.createCell(8).setCellValue("学校名称");

            IntStream.range(0, schoolGroupDOList.size()).forEach(idx -> {
                Row row = sheet.createRow(idx + 1);

                SchoolGroupDO schoolGroupDO = schoolGroupDOList.get(idx);
                String createdAt = LocalDateTime.
                        ofInstant(Instant.ofEpochSecond(Long.valueOf(schoolGroupDO.getCreatedAt())), ZoneId.of("UTC+08:00")).
                        format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                String dismissedAt = schoolGroupDO.isEnable() ? "" : LocalDateTime.
                        ofInstant(Instant.ofEpochSecond(Long.valueOf(schoolGroupDO.getDismissedAt())), ZoneId.of("UTC+08:00")).
                        format(DateTimeFormatEnum.COMMON.getDateTimeFormatter());
                row.createCell(0).setCellValue(schoolGroupDO.getGroupId());
                row.createCell(1).setCellValue(schoolGroupDO.getGroupName());
                row.createCell(2).setCellValue(addressService.address(Lists.newArrayList(schoolGroupDO.getGroupAddrCode().split("_"))) + schoolGroupDO.getGroupAddrDetail());
                row.createCell(3).setCellValue(schoolGroupDO.getGroupBrief());
                row.createCell(4).setCellValue(schoolGroupDO.getGroupMemCount());
                row.createCell(5).setCellValue(createdAt);
                row.createCell(6).setCellValue(dismissedAt);
                row.createCell(7).setCellValue(schoolGroupDO.isEnable() ? "ENABLE" : "DISMISS");
                row.createCell(8).setCellValue(schoolGroupDO.getSchoolName());
            });
            LocalDate localDate = LocalDate.now();
            String basePath = customEnvironmentConfig.getUploadLocation() + customEnvironmentConfig.getExcelLocation();
            String filename = fileUtil.filename(basePath, "xls", localDate.format(DateTimeFormatEnum.COMMON_DATE.getDateTimeFormatter()) + "-" + "校友群-");
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

    private SchoolGroupInformationQuery asSchoolGroupInformationQuery(GroupInformationQuery groupInformationQuery) {
        if (!(groupInformationQuery instanceof SchoolGroupInformationQuery)) {
            throw new ClassCastException();
        }
        return (SchoolGroupInformationQuery) groupInformationQuery;
    }

    /**
     * 检验当前用户是否是当前群的管理人员
     *
     * @param groupId 群ID
     */
    private void groupAdmin(long groupId) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        if (schoolGroupDao.isExistedByUserIdAndGroupId(currentUserDO.getUserId(), groupId) == null) {
            throw new InvalidParameterException("当前用户不是当前群的管理员");
        }
    }


}
