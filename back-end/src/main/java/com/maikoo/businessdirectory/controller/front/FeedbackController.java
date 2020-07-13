package com.maikoo.businessdirectory.controller.front;

import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.query.FeedbackQuery;
import com.maikoo.businessdirectory.service.FeedbackService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@ResponseBody
@RequestMapping("/api/feedback")
public class FeedbackController {
    @Autowired
    private FeedbackService feedbackService;

    @RequestMapping("/add")
    public ResponseDTO insert(FeedbackQuery feedbackQuery){
        feedbackService.insert(feedbackQuery);
        return new ResponseDTO(200, "提交成功");
    }
}
