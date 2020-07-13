package com.maikoo.businessdirectory.util;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.maikoo.businessdirectory.config.CustomEnvironmentConfig;
import com.maikoo.businessdirectory.dao.FormIdDao;
import com.maikoo.businessdirectory.model.FormIdDO;
import com.maikoo.businessdirectory.model.UserDO;
import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.model.constant.ReviewStatusEnum;
import com.maikoo.businessdirectory.model.dto.BaseMessageDTO;
import com.maikoo.businessdirectory.model.dto.MessageDataDTO;
import com.maikoo.businessdirectory.model.dto.MiniProgramMessageDTO;
import com.maikoo.businessdirectory.model.dto.OfficialMessageDTO;
import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

@Slf4j
@Component
public class TemplateMessageUtil {
    @Autowired
    private FormIdDao formIdDao;
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;
    @Autowired
    private WechatUtil wechatUtil;
    @Autowired
    private ObjectMapper objectMapper;

    public MiniProgramMessageDTO applyResultMessageDTO(GroupTypeEnum groupType, UserDO applyUserDO, long applyId, String groupName, String dateTime, ReviewStatusEnum reviewStatus) {
        String page = "pages/index/index?action=redirect&page=apply&gtype=" + groupType.toString() + "&applyid=" + applyId;
        Map<String, MessageDataDTO> data = wechatUtil.miniApplyResultData(dateTime, reviewStatus, groupName);
        return miniProgramMessageDTO(applyUserDO.getOpenid(), customEnvironmentConfig.getMiniApplyResultTemplateId(), page, data);
    }

//    public void applyResult(GroupTypeEnum groupType, UserDO applyUserDO, long applyId, String groupName, String dateTime, ReviewStatusEnum reviewStatus) {
//        miniProgramByFormId(applyUserDO.getUserId(), applyResultMessageDTO(groupType, applyUserDO, applyId, groupName, dateTime, reviewStatus));
//    }

    public void applyResult(GroupTypeEnum groupType, UserDO applyUserDO, long applyId, long groupId, String groupName, String brief, String dateTime, ReviewStatusEnum reviewStatus) {
        MiniProgramMessageDTO applyResultMessageDTO = applyResultMessageDTO(groupType, applyUserDO, applyId, groupName, dateTime, reviewStatus);

        Map<String, MessageDataDTO> communityApplyData = wechatUtil.miniCommunityApplySuccessData(groupName, dateTime, brief);
        String page = "pages/index/index?action=redirect&page=gdetail&gtype=" + groupType.toString() + "&gid=" + groupId;
        MiniProgramMessageDTO communityApplyMessageDTO = miniProgramMessageDTO(applyUserDO.getOpenid(), customEnvironmentConfig.getMiniCommunityApplyTemplateId(), page, communityApplyData);

        applyResultByFormId(applyUserDO.getUserId(), reviewStatus, applyResultMessageDTO, communityApplyMessageDTO);
    }

    public void review(String openid, String name, String groupName, String dateTime, int reviewSize) {
        BaseMessageDTO baseMessageDTO = officialMessageDTO(openid, customEnvironmentConfig.getReviewTemplateId(), wechatUtil.officialReviewData(groupName, name, dateTime, reviewSize));
        baseMessageDTO.getMpTemplateMsg().getMiniprogram().put("pagepath", "pages/index/index?action=redirect&page=approve");
        wechatUtil.officialMessage(baseMessageDTO);
    }

    public void dismiss(String openid, String groupName, String dateTime) {
        BaseMessageDTO baseMessageDTO = officialMessageDTO(openid, customEnvironmentConfig.getDismissTemplateId(), wechatUtil.officialDismissData(groupName, dateTime));
        wechatUtil.officialMessage(baseMessageDTO);
    }

    public void changeOwner(UserDO changedUserDO, String groupName, String changedOwnerName) {
        MiniProgramMessageDTO miniProgramMessageDTO = new MiniProgramMessageDTO();
        miniProgramMessageDTO.setTouser(changedUserDO.getOpenid());
        miniProgramMessageDTO.setData(wechatUtil.miniChangeOwnerData(groupName));
        miniProgramMessageDTO.setTemplateId(customEnvironmentConfig.getChangeOwnerTemplateId());
        miniProgramByFormId(changedUserDO.getUserId(), miniProgramMessageDTO);


        BaseMessageDTO baseMessageDTO = officialMessageDTO(changedUserDO.getOpenid(), customEnvironmentConfig.getChangeOwnerTemplateId(), wechatUtil.officialChangeOwnerData(groupName, changedOwnerName));
        wechatUtil.officialMessage(baseMessageDTO);
    }

    private List<FormIdDO> formIdList(long userId) {
        FormIdDO queryFormIdDO = new FormIdDO();
        queryFormIdDO.setUserId(userId);
        queryFormIdDO.setExpireAt(System.currentTimeMillis() / 1000);
        queryFormIdDO.setUsed(false);

        return formIdDao.selectByUserIdAndExpireAtAndIsUsed(queryFormIdDO);
    }

    private void miniProgramByFormId(long userId, MiniProgramMessageDTO miniProgramMessageDTO) {
        List<FormIdDO> formIdDOList = formIdList(userId);

        formIdDOList.stream().anyMatch(formIdDO -> {
            miniProgramMessageDTO.setFormId(formIdDO.getFormId());
            if (wechatUtil.miniProgramMessage(miniProgramMessageDTO)) {
                formIdDao.updateIsUsed(formIdDO.getIdx(), true);
                return true;
            } else {
                return false;
            }
        });
    }

    private void applyResultByFormId(long userId, ReviewStatusEnum reviewStatus, MiniProgramMessageDTO applyResultMessageDTO, MiniProgramMessageDTO communityApplyMessageDTO) {
        List<FormIdDO> formIdDOList = formIdList(userId);

        boolean applyResultFlag = false;
        boolean applyFlag = false;

        for (FormIdDO formIdDO : formIdDOList) {
            applyResultMessageDTO.setFormId(formIdDO.getFormId());
            if (!applyResultFlag) {
                if (wechatUtil.miniProgramMessage(applyResultMessageDTO)) {
                    applyResultFlag = true;
                }
            } else if (reviewStatus.equals(ReviewStatusEnum.APPROVE) && !applyFlag) {
                communityApplyMessageDTO.setFormId(formIdDO.getFormId());
                if (wechatUtil.miniProgramMessage(communityApplyMessageDTO)) {
                    applyFlag = true;
                }
            } else {
                return;
            }
            log.info("小程序发送模版消息。form id: {}", formIdDO.getIdx());
            formIdDao.updateIsUsed(formIdDO.getIdx(), true);
        }
    }

    private MiniProgramMessageDTO miniProgramMessageDTO(String openid, String templateId, String page, Map<String, MessageDataDTO> data) {
        MiniProgramMessageDTO miniProgramMessageDTO = new MiniProgramMessageDTO();
        miniProgramMessageDTO.setTouser(openid);
        miniProgramMessageDTO.setData(data);
        miniProgramMessageDTO.setTemplateId(templateId);
        miniProgramMessageDTO.setPage(page);
        return miniProgramMessageDTO;
    }

    private BaseMessageDTO officialMessageDTO(String openid, String templateId, Map<String, MessageDataDTO> data) {
        Map<String, String> miniProgramData = new HashMap<>();
        miniProgramData.put("appid", customEnvironmentConfig.getMiniAppId());

        OfficialMessageDTO officialMessageDTO = new OfficialMessageDTO();
        officialMessageDTO.setAppid(customEnvironmentConfig.getAppId());
        officialMessageDTO.setTemplateId(templateId);
        officialMessageDTO.setUrl("");
        officialMessageDTO.setMiniprogram(miniProgramData);
        officialMessageDTO.setData(data);

        BaseMessageDTO baseMessageDTO = new BaseMessageDTO();
        baseMessageDTO.setTouser(openid);
        baseMessageDTO.setMpTemplateMsg(officialMessageDTO);

        return baseMessageDTO;
    }
}
