package com.maikoo.superminercions.service.impl;

import com.github.pagehelper.PageHelper;
import com.maikoo.superminercions.config.CustomEnvironmentConfig;
import com.maikoo.superminercions.dao.NewsDao;
import com.maikoo.superminercions.model.NewsDO;
import com.maikoo.superminercions.model.dto.NewsBackDTO;
import com.maikoo.superminercions.model.dto.NewsDTO;
import com.maikoo.superminercions.model.dto.NewsInformationDTO;
import com.maikoo.superminercions.model.query.NewsQuery;
import com.maikoo.superminercions.service.NewsService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.ArrayList;
import java.util.List;

@Service
public class NewsServiceImpl implements NewsService {

    @Autowired
    private NewsDao newsDao;

    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;

    @Override
    public NewsInformationDTO information(long id) {
        NewsInformationDTO newsInformationDTO = null;

        NewsDO newsDO = newsDao.selectOne(id);
        if (newsDO == null) {
            newsInformationDTO = new NewsInformationDTO();
        } else {
            newsInformationDTO = NewsInformationDTO.valueOf(newsDO);
        }

        return newsInformationDTO;
    }

    @Override
    public List<NewsDTO> list(int pageNumber) {
        List<NewsDTO> newsDTOList = new ArrayList<>();

        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = newsDao.selectPageIds();

        if (ids != null && ids.size() > 0) {
            List<NewsDO> newsDOList = newsDao.selectByIds(ids);
            newsDOList.forEach(newsDO -> newsDTOList.add(NewsDTO.valueOf(newsDO)));
        }

        return newsDTOList;
    }

    @Override
    public List<NewsBackDTO> getNewList() {
        List<NewsDO> newsDOS = newsDao.selectAllNews();
        List<NewsBackDTO> newsDTOS = new ArrayList<>();
        if(newsDOS!=null&&newsDOS.size()>0){
            newsDOS.forEach(newsDO ->newsDTOS.add(NewsBackDTO.valueOf(newsDO)));
        }
        return newsDTOS;
    }

    @Override
    public void removeNews(int newsId) {
        newsDao.removeNews(newsId);
    }

    @Override
    public void addNews(NewsQuery newsQuery) {
        newsDao.addNews(newsQuery);
    }

    @Override
    public void updateNewsStatus(int newsId,int status) {
        newsDao.updateNewsStatus(newsId,status);
    }
}
