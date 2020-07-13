package com.maikoo.businessdirectory.model.dto;

import com.fasterxml.jackson.databind.annotation.JsonSerialize;
import com.maikoo.businessdirectory.model.FeedbackDO;
import com.maikoo.businessdirectory.model.serializer.BaseDateTimeSerializer;
import lombok.Data;

@Data
public class FeedbackDTO {
    private long fbId;
    private String userName;
    private String fbImgUrl;
    private String fbContent;
    private String userMobile;
    @JsonSerialize(using = BaseDateTimeSerializer.class)
    private String fbAt;

    public static FeedbackDTO valueOf(FeedbackDO feedbackDO){
        FeedbackDTO feedbackDTO = new FeedbackDTO();
        feedbackDTO.setFbId(feedbackDO.getFbId());
        feedbackDTO.setFbAt(feedbackDO.getCreatedAt() != 0 ? String.valueOf(feedbackDO.getCreatedAt()) : "");
        feedbackDTO.setFbContent(feedbackDO.getContent());
        feedbackDTO.setFbImgUrl(feedbackDO.getImageUrl() != null ? feedbackDO.getImageUrl() : "");
        if (feedbackDO.getUserDO() != null) {
            feedbackDTO.setUserName(feedbackDO.getUserDO().getNickname());
        }
        feedbackDTO.setUserMobile(feedbackDO.getMobile());
        return feedbackDTO;
    }

}
