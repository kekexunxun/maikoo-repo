package com.maikoo.superminercions.controller.back;

import com.maikoo.superminercions.model.dto.NewsBackDTO;
import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.model.query.NewsQuery;
import com.maikoo.superminercions.service.NewsService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller
@ResponseBody
@RequestMapping("api/admin/news")
public class NewsBackController {

    @Autowired
    private NewsService newsService;

    @RequestMapping("/add")
    public ResponseDTO addNews(@Validated NewsQuery newsQuery){
        newsService.addNews(newsQuery);
        return  new ResponseDTO(200,"添加成功", null);
    }

    @RequestMapping("/delete")
    public ResponseDTO removeNews(@RequestParam("newsId") int newsId){
        newsService.removeNews(newsId);
        return new ResponseDTO(200,"删除成功", null);
    }

    @RequestMapping("/list")
    public ResponseDTO<List<NewsBackDTO>> getNewsList(){
        return new ResponseDTO<>(200,"query newsList success",newsService.getNewList());
    }

    /**
     * 更新新闻的状态
     * @param newsId
     * @param status 有两种状态 1 表示展示 0 表示不展示
     * @return
     */
    @RequestMapping("/update")
    public ResponseDTO updateNews(@RequestParam("newsId") int newsId,@RequestParam("status") int status){
        newsService.updateNewsStatus(newsId,status);
        return new ResponseDTO(200,"update success",null);
    }

}
