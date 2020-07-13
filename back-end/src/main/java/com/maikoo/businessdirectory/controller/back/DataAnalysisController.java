package com.maikoo.businessdirectory.controller.back;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.model.dto.GroupDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.view.GroupAnalysisView;
import com.maikoo.businessdirectory.service.GroupDataAnalysisService;
import org.apache.ibatis.annotations.Param;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/admin/api/admin/analysis")
public class DataAnalysisController {
    @Autowired
    private GroupDataAnalysisService groupDataAnalysisService;

    @JsonView(GroupAnalysisView.Public.class)
    @RequestMapping("/group")
    public ResponseDTO<List<GroupDTO>> groupData(@Param("sType") String sType) {
        return new ResponseDTO<>(200, "获取成功", groupDataAnalysisService.getGroupData(sType));
    }

    @JsonView(GroupAnalysisView.Public.class)
    @RequestMapping("/user")
    public ResponseDTO<List<GroupDTO>> groupUserData(@Param("sType") String sType) {
        return new ResponseDTO<>(200, "获取成功", groupDataAnalysisService.getGroupUserData(sType));
    }


}
