package com.maikoo.businessdirectory.controller.back;

import com.maikoo.businessdirectory.model.dto.FeedbackDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.service.FeedbackService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller
@RequestMapping(value = "/admin/api/feedback")
public class FeedBackController {

    @Autowired
    private FeedbackService feedbackService;

    @ResponseBody
    @RequestMapping(value = "/list")
    public ResponseDTO<List<FeedbackDTO>> list() {
        return new ResponseDTO<>(200, "success", feedbackService.adminGetFeedBack());
    }
}
