package com.maikoo.businessdirectory.controller.back;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.factory.GroupFactory;
import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.model.dto.FileDTO;
import com.maikoo.businessdirectory.model.dto.GroupDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.query.GroupQuery;
import com.maikoo.businessdirectory.model.view.GroupView;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestAttribute;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

import javax.servlet.http.HttpServletRequest;
import java.util.List;

@Controller("AdminGroupController")
@RequestMapping("/admin/api/group")
public class GroupController {
    @Autowired
    private GroupFactory groupFactory;

    @JsonView(GroupView.AdminSchool.class)
    @ResponseBody
    @RequestMapping("/school")
    public ResponseDTO<List<GroupDTO>> schoolList(){
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(GroupTypeEnum.SCHOOL).selectAllByAdmin());
    }

    @JsonView(GroupView.AdminClass.class)
    @ResponseBody
    @RequestMapping("/class")
    public ResponseDTO<List<GroupDTO>> classList(){
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(GroupTypeEnum.CLASS).selectAllByAdmin());
    }

    @JsonView(GroupView.AdminCommunity.class)
    @ResponseBody
    @RequestMapping("/community")
    public ResponseDTO<List<GroupDTO>> communityList(){
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(GroupTypeEnum.COMMUNITY).selectAllByAdmin());
    }

    @JsonView(GroupView.AdminCountry.class)
    @ResponseBody
    @RequestMapping("/country")
    public ResponseDTO<List<GroupDTO>> countryList(){
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(GroupTypeEnum.COUNTRY).selectAllByAdmin());
    }

    @RequestMapping("/information")
    public String information(GroupQuery groupQuery, HttpServletRequest request) {
        request.setAttribute("groupQuery", groupQuery);
        return "forward:/admin/api/group/"+groupQuery.getGroupType().toString().toLowerCase()+"/information";
    }

    @JsonView(GroupView.AdminClassInformation.class)
    @ResponseBody
    @RequestMapping("/class/information")
    public ResponseDTO<GroupDTO> classInformation(@RequestAttribute("groupQuery") GroupQuery groupQuery) {
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(groupQuery.getGroupType()).informationByAdmin(groupQuery.getGroupId()));
    }

    @JsonView(GroupView.AdminSchoolInformation.class)
    @ResponseBody
    @RequestMapping("/school/information")
    public ResponseDTO<GroupDTO> schoolInformation(@RequestAttribute("groupQuery") GroupQuery groupQuery) {
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(groupQuery.getGroupType()).informationByAdmin(groupQuery.getGroupId()));
    }

    @JsonView(GroupView.AdminCountryInformation.class)
    @ResponseBody
    @RequestMapping("/country/information")
    public ResponseDTO<GroupDTO> countryInformation(@RequestAttribute("groupQuery") GroupQuery groupQuery) {
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(groupQuery.getGroupType()).informationByAdmin(groupQuery.getGroupId()));
    }

    @JsonView(GroupView.AdminCommunityInformation.class)
    @ResponseBody
    @RequestMapping("/community/information")
    public ResponseDTO<GroupDTO> communityInformation(@RequestAttribute("groupQuery") GroupQuery groupQuery) {
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(groupQuery.getGroupType()).informationByAdmin(groupQuery.getGroupId()));
    }

    @ResponseBody
    @RequestMapping("/excel")
    public ResponseDTO<FileDTO> excel(GroupTypeEnum groupType){
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(groupType).excel());
    }
}

