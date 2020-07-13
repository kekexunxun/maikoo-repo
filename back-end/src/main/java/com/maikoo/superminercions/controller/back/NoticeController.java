package com.maikoo.superminercions.controller.back;

import com.maikoo.superminercions.model.dto.NoticeBackDTO;
import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.model.query.NoticeQuery;
import com.maikoo.superminercions.service.NoticeService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@ResponseBody
@RequestMapping("/api/admin/notice")
public class NoticeController {
    @Autowired
    private NoticeService noticeService;

    @RequestMapping("/add")
    public ResponseDTO insert(@Validated NoticeQuery noticeQuery) {
        noticeService.update(noticeQuery);
        return new ResponseDTO(200, "添加成功", null);
    }

    @RequestMapping("/information")
    public ResponseDTO<NoticeBackDTO> information() {
        return new ResponseDTO<>(200, "获取成功", noticeService.information());
    }
}
