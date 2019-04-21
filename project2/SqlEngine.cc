/**
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */

#include <cstdio>
#include <cstring>
#include <cstdlib>
#include <iostream>
#include <fstream>
#include "Bruinbase.h"
#include "SqlEngine.h"
#include "BTreeIndex.h"
#include <climits>



using namespace std;

// external functions and variables for load file and sql command parsing 
extern FILE* sqlin;
int sqlparse(void);


RC SqlEngine::run(FILE* commandline)
{
  fprintf(stdout, "Bruinbase> ");

  // set the command line input and start parsing user input
  sqlin = commandline;
  sqlparse();  // sqlparse() is defined in SqlParser.tab.c generated from
               // SqlParser.y by bison (bison is GNU equivalent of yacc)

  return 0;
}

RC SqlEngine::select(int attr, const string& table, const vector<SelCond>& cond)
{
  RecordFile rf;   // RecordFile containing the table
  RecordId   rid;  // record cursor for table scanning

  BTreeIndex bti;  // btreeindex if table has a b+ tree

  vector<SelCond> cond1;
  vector<SelCond> cond2;
  vector<int> neKey;
  int minKey = INT_MIN;
  int maxKey = INT_MAX;
  int eqKey;
  bool ltFlag = false;
  bool gtFlag = false;
  IndexCursor cursor;

  RC     rc;
  int    key;     
  string value;
  int    count;
  int    diff;

  // open the table file
  if ((rc = rf.open(table + ".tbl", 'r')) < 0) {
    fprintf(stderr, "Error: table %s does not exist\n", table.c_str());
    return rc;
  }

  for (int i = 0; i < cond.size(); i++) {
    if(cond[i].attr == 1){
      if(cond[i].comp == SelCond::NE)
        neKey.push_back(atoi(cond[i].value));
      else
        cond1.push_back(cond[i]);
    }
    else if(cond[i].attr == 2)
      cond2.push_back(cond[i]);
  }

  if((cond1.size()>0 || (cond1.size() == 0 && cond2.size() == 0 && (attr == 1 || attr == 4))) && (rc = bti.open(table + ".idx", 'r')) == 0) {

    count = 0;

    for(int i = 0; i < cond1.size(); i++){
      switch(cond1[i].comp){
        case SelCond::EQ:{
          eqKey = atoi(cond1[i].value);
          if((eqKey < maxKey && eqKey > minKey) || 
            (eqKey==minKey && eqKey<maxKey && gtFlag== false) || 
            (eqKey > minKey && eqKey == maxKey && ltFlag==false) || 
            (eqKey == minKey && eqKey == maxKey && (gtFlag==false || ltFlag==false))){
            minKey =maxKey=eqKey;
            gtFlag=ltFlag=false;
          }
          else goto close_index;
          break;
        }

        //case SelCond::NE:
        //break;
        case SelCond::GT:
        minKey = (minKey > atoi(cond1[i].value)) ? minKey : atoi(cond1[i].value);
        gtFlag = true;
        break;
        case SelCond::LT:
        maxKey = (maxKey < atoi(cond1[i].value)) ? maxKey : atoi(cond1[i].value);
        ltFlag = true;
        break;
        case SelCond::GE:
        minKey = (minKey > atoi(cond1[i].value)) ? minKey : atoi(cond1[i].value);
        gtFlag = false;
        break;
        case SelCond::LE:
        maxKey = (maxKey < atoi(cond1[i].value)) ? maxKey : atoi(cond1[i].value);
        ltFlag = false;
        break;
      }
    }

    if((minKey>maxKey) || (minKey==maxKey && (ltFlag==true || gtFlag==true)))
      goto close_index;
    bti.locate(minKey, cursor);

    read_record:
    while((rc = bti.readForward(cursor, key, rid)) != RC_INVALID_CURSOR && key <= maxKey){
      
      for(int i = 0; i < neKey.size(); i++)
        if(key == neKey[i])
          goto read_record;


      if((attr == 2 || attr == 3 || cond2.size() > 0)  && (rc = rf.read(rid, key, value)) == 0){
        for(int i = 0; i<cond2.size(); i++){
          diff = strcmp(value.c_str(), cond2[i].value);
          switch(cond2[i].comp){
            case SelCond::EQ:
            if (diff != 0) goto read_record;
            break;
            case SelCond::NE:
            if (diff == 0) goto read_record;
            break;
            case SelCond::GT:
            if (diff <= 0) goto read_record;
            break;
            case SelCond::LT:
            if (diff >= 0) goto read_record;
            break;
            case SelCond::GE:
            if (diff < 0) goto read_record;
            break;
            case SelCond::LE:
            if (diff > 0) goto read_record;
            break;
          }
        }
      }
      count++;
              // all condition satisfied
      if((gtFlag && key == minKey) || (ltFlag && key == maxKey))
        continue;
      else {
        //fprintf(stdout, "count = %d\n", count);
        switch (attr){
          case 1:  // SELECT key
          fprintf(stdout, "%d\n", key);
          break;
          case 2:  // SELECT value
          fprintf(stdout, "%s\n", value.c_str());
          break;
          case 3:  // SELECT *
          fprintf(stdout, "%d '%s'\n", key, value.c_str());
          break;
        }
      }
    }
    if (attr == 4)
      fprintf(stdout, "%d\n", count);
    close_index:
    rc = bti.close();
  }


  /*** No index file used ***/
  else{

    // scan the table file from the beginning
    rid.pid = rid.sid = 0;
    count = 0;
    while (rid < rf.endRid()) {
      // read the tuple
      if ((rc = rf.read(rid, key, value)) < 0) {
        fprintf(stderr, "Error: while reading a tuple from table %s\n", table.c_str());
        goto exit_select;
      }

      // check the conditions on the tuple
      for (unsigned i = 0; i < cond.size(); i++) {
        // compute the difference between the tuple value and the condition value
        switch (cond[i].attr) {
          case 1:
          diff = key - atoi(cond[i].value);
          break;
          case 2:
          diff = strcmp(value.c_str(), cond[i].value);
          break;
        }

        // skip the tuple if any condition is not met
        switch (cond[i].comp) {
          case SelCond::EQ:
          if (diff != 0) goto next_tuple;
          break;
          case SelCond::NE:
          if (diff == 0) goto next_tuple;
          break;
          case SelCond::GT:
          if (diff <= 0) goto next_tuple;
          break;
          case SelCond::LT:
          if (diff >= 0) goto next_tuple;
          break;
          case SelCond::GE:
          if (diff < 0) goto next_tuple;
          break;
          case SelCond::LE:
          if (diff > 0) goto next_tuple;
          break;
        }
      }

      // the condition is met for the tuple. 
      // increase matching tuple counter
      count++;

        // print the tuple 
      switch (attr) {
          case 1:  // SELECT key
          fprintf(stdout, "%d\n", key);
          break;
          case 2:  // SELECT value
          fprintf(stdout, "%s\n", value.c_str());
          break;
          case 3:  // SELECT *
          fprintf(stdout, "%d '%s'\n", key, value.c_str());
          break;
        }

      // move to the next tuple
      next_tuple:
      ++rid;
    }

    // print matching tuple count if "select count(*)"
    if (attr == 4) {
      fprintf(stdout, "%d\n", count);
    }
    rc = 0;
  }

  exit_select:
  rf.close();
  return rc;
}

RC SqlEngine::load(const string& table, const string& loadfile, bool index)
{
  /* your code here */

  // variables needed
  BTreeIndex bti;
  RecordFile rf;
  RecordId rid;

  RC rc;
  int key;
  string value;
  string line;


  // open load file with 'r'
  ifstream file(loadfile.c_str());  

  if(file.is_open()) {
    if(index){
      // open/create index with 'w'
      if ((rc = bti.open(table + ".idx", 'w')) < 0) {
        fprintf(stderr, "Error: unable to create/write BTreeIndex %s \n", table.c_str());
        return rc;
      }
    }
    // open/create table with 'w'
    if ((rc = rf.open(table + ".tbl", 'w')) < 0) {
      fprintf(stderr, "Error: unable to create/write table %s \n", table.c_str());
      return rc;
    }
      // for each line in loadfile
    while(getline(file, line)){
        // extract corresponding key and value
      rc = parseLoadLine(line, key, value);
        // get end id in recordfile
      rid = rf.endRid();
        // append a new key-value pair onto into the record file
      rc = rf.append(key, value, rid);
      if(index)
        rc = bti.insert(key, rid);
    }
    if(index)
      rc = bti.close();
    rc = rf.close(); 
    file.close();  
  }
  else {
    // if loadfile not exist, report error and return func
    fprintf(stderr, "Error: Loadfile %s does not exist\n", loadfile.c_str());
    return 0;
  }

  fprintf(stdout, "data in loadfile %s is successfully loaded into table %s\n", loadfile.c_str(), table.c_str());
  return rc;
}

RC SqlEngine::parseLoadLine(const string& line, int& key, string& value)
{
  const char *s;
  char        c;
  string::size_type loc;

    // ignore beginning white spaces
  c = *(s = line.c_str());
  while (c == ' ' || c == '\t') { c = *++s; }

    // get the integer key value
  key = atoi(s);

    // look for comma
  s = strchr(s, ',');
  if (s == NULL) { return RC_INVALID_FILE_FORMAT; }

    // ignore white spaces
  do { c = *++s; } while (c == ' ' || c == '\t');

    // if there is nothing left, set the value to empty string
  if (c == 0) { 
    value.erase();
    return 0;
  }

    // is the value field delimited by ' or "?
  if (c == '\'' || c == '"') {
    s++;
  } else {
    c = '\n';
  }

    // get the value string
  value.assign(s);
  loc = value.find(c, 0);
  if (loc != string::npos) { value.erase(loc); }

  return 0;
}
