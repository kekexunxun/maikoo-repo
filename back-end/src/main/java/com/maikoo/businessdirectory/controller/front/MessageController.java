package com.maikoo.businessdirectory.controller.front;

import com.maikoo.businessdirectory.model.dto.MessageDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.service.MessageService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import javax.servlet.http.HttpSession;
import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/api/message")
public class MessageController {
    @Autowired
    private MessageService messageService;
    @Autowired
    private HttpSession session;

    @RequestMapping("/user")
    public ResponseDTO<List<MessageDTO>> listForUser(@RequestParam(name = "pageNum", defaultValue = "1", required = false) int pageNumber) {
        return new ResponseDTO<>(200, "获取成功", messageService.listForUser(pageNumber));
    }

}
