package com.maikoo.superminercions.controller.front;

import com.maikoo.superminercions.model.dto.NewsDTO;
import com.maikoo.superminercions.model.dto.NewsInformationDTO;
import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.service.NewsService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/api/news")
public class NewsController {
    @Autowired
    private NewsService newsService;

    @RequestMapping("/information")
    public ResponseDTO<NewsInformationDTO> information(long newsId) {
        return new ResponseDTO<>(200, "获取成功", newsService.information(newsId));
    }

    @RequestMapping
    public ResponseDTO<List<NewsDTO>> list(@RequestParam(name = "pageNum", defaultValue = "1", required = false) int pageNumber) {
        return new ResponseDTO<>(200, "获取成功", newsService.list(pageNumber));
    }
}
