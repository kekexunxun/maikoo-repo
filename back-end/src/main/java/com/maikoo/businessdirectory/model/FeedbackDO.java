package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.query.FeedbackQuery;
import lombok.Data;

@Data
public class FeedbackDO {
    private long fbId;
    private UserDO userDO;
    private String content;
    private String mobile;
    private String imageUrl;
    private long createdAt;

    public static FeedbackDO valueOf(FeedbackQuery feedbackQuery) {
        FeedbackDO feedbackDO = new FeedbackDO();
        feedbackDO.setContent(feedbackQuery.getFeedback());
        feedbackDO.setMobile(feedbackQuery.getMobile());
        feedbackDO.setImageUrl(feedbackQuery.getImgUrl());
        return feedbackDO;
    }
}
