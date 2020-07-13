package com.maikoo.businessdirectory.controller.front;

import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.query.FormIdQuery;
import com.maikoo.businessdirectory.service.FormIdService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/api/form-id")
public class FormIdController {
    @Autowired
    private FormIdService formIdService;

    @RequestMapping("/insert")
    public ResponseDTO insert(@RequestBody List<FormIdQuery> formIdQueryList){
        formIdService.insert(formIdQueryList);
        return new ResponseDTO(200, "success");
    }
}
