package com.maikoo.businessdirectory.controller.front;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.model.dto.FlagDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.view.FlagView;
import com.maikoo.businessdirectory.service.InformationService;
import com.maikoo.businessdirectory.service.MessageService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@ResponseBody
@RequestMapping("/api/flag")
public class FlagController {

    @Autowired
    private InformationService informationService;

    @Autowired
    private MessageService messageService;

    @JsonView(FlagView.User.class)
    @RequestMapping("/apply")
    public ResponseDTO<FlagDTO> userHasNewMessage() {
        return new ResponseDTO<>(200, "success", informationService.userHasNewMessage());
    }

    @JsonView(FlagView.User.class)
    @RequestMapping("/review")
    public ResponseDTO<FlagDTO> adminHasNewMessage() {
        return new ResponseDTO<>(200, "success", informationService.adminHasNewMessage());
    }
    @JsonView(FlagView.System.class)
    @RequestMapping("/message")
    public ResponseDTO<FlagDTO> hasNewMessage() {
        return new ResponseDTO<>(200, "获取成功", informationService.hasNewMessage());
    }


}
