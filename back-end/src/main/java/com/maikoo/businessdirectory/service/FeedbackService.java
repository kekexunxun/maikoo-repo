package com.maikoo.businessdirectory.service;

import com.maikoo.businessdirectory.model.dto.FeedbackDTO;
import com.maikoo.businessdirectory.model.query.FeedbackQuery;

import java.util.List;

public interface FeedbackService {
    void insert(FeedbackQuery feedbackQuery);

    List<FeedbackDTO> adminGetFeedBack();

}
